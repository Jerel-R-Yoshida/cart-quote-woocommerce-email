#!/usr/bin/env python3
"""
Pre-push Hook for Cart Quote WooCommerce & Email

Triggers:
  - git push origin dev    → Update README.md (prompt user)
  - git push origin master → Schedule Wiki update (parse commits)

Author: Jerel Yoshida
Version: 1.0.0
"""

import sys
import os
import subprocess
import json
from pathlib import Path
from datetime import datetime

if sys.platform == 'win32':
    import codecs
    try:
        if hasattr(sys.stdout, 'buffer'):
            sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')
        if hasattr(sys.stderr, 'buffer'):
            sys.stderr = codecs.getwriter('utf-8')(sys.stderr.buffer, 'strict')
    except Exception:
        pass

BUILD_DIR = Path(__file__).parent.parent
CONFIG = {
    'enabled': True,
    'dev_branch': 'dev',
    'master_branch': 'master',
    'changelog_limit': 5,
    'wiki_path': 'D:/Projects/cart-quote-woocommerce-email.wiki',
    'skip_keywords': ['[skip docs]', '[no docs]', '[skip hook]'],
    'dev_update_readme': True,
    'master_update_wiki': True,
}

def load_config():
    global CONFIG
    config_path = BUILD_DIR / 'hook-config.json'
    if config_path.exists():
        with open(config_path, 'r', encoding='utf-8') as f:
            loaded = json.load(f)
            CONFIG.update(loaded)

def import_update_docs():
    update_docs_path = BUILD_DIR / 'update_docs.py'
    if not update_docs_path.exists():
        return None
    
    import importlib.util
    spec = importlib.util.spec_from_file_location('update_docs', update_docs_path)
    if spec is None or spec.loader is None:
        return None
    update_docs = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(update_docs)
    return update_docs

def get_push_info():
    pushes = []
    try:
        stdin_data = sys.stdin.read().strip()
        if stdin_data:
            for line in stdin_data.split('\n'):
                if line.strip():
                    parts = line.split()
                    if len(parts) >= 4:
                        pushes.append({
                            'local_ref': parts[0],
                            'local_sha': parts[1],
                            'remote_ref': parts[2],
                            'remote_sha': parts[3],
                            'branch': parts[2].replace('refs/heads/', '')
                        })
    except Exception:
        pass
    return pushes

def get_commits_being_pushed(local_sha, remote_sha):
    try:
        if remote_sha == '0' * 40:
            cmd = ['git', 'log', '--pretty=format:%s', f'{local_sha}']
        else:
            cmd = ['git', 'log', '--pretty=format:%s', f'{remote_sha}..{local_sha}']
        
        result = subprocess.run(cmd, capture_output=True, text=True, cwd=BUILD_DIR.parent)
        if result.returncode == 0 and result.stdout.strip():
            return result.stdout.strip().split('\n')
    except Exception:
        pass
    return []

def check_skip_keyword(commits):
    for commit in commits:
        for keyword in CONFIG['skip_keywords']:
            if keyword.lower() in commit.lower():
                return True
    return False

def get_current_version():
    plugin_file = BUILD_DIR.parent / 'cart-quote-woocommerce-email.php'
    try:
        with open(plugin_file, 'r', encoding='utf-8') as f:
            for line in f:
                if line.strip().startswith('* Version:'):
                    return line.split(':')[1].strip()
    except Exception:
        pass
    return 'unknown'

def get_icon(change_type):
    icons = {
        'fix': '🐛', 'feature': '✨', 'enhancement': '🔧',
        'performance': '🚀', 'documentation': '📝', 'security': '🔒'
    }
    return icons.get(change_type, '📦')

def prompt_changelog():
    print("\n" + "=" * 50)
    print("📝 DOCUMENTATION UPDATE")
    print("=" * 50)
    
    try:
        print("\n❓ Changelog for this push (or press Enter to skip):")
        changelog = input("   > ").strip()
    except (EOFError, KeyboardInterrupt):
        print("\n⏭️  Skipping documentation update")
        return None
    
    if not changelog:
        print("⏭️  Skipping documentation update")
        return None
    
    print("\n❓ Change type:")
    print("   [1] 🐛 fix       [2] ✨ feature  [3] 🔧 enhancement")
    print("   [4] 🚀 perform   [5] 📝 docs     [6] 🔒 security")
    try:
        choice = input("   > ").strip()
    except (EOFError, KeyboardInterrupt):
        choice = "1"
    
    type_map = {
        '1': 'fix', '2': 'feature', '3': 'enhancement',
        '4': 'performance', '5': 'documentation', '6': 'security'
    }
    change_type = type_map.get(choice, 'fix')
    
    return {
        'changelog': changelog,
        'change_type': change_type,
        'details': '',
        'full_changelog': f"{get_icon(change_type)} {changelog}"
    }

def parse_commits_for_changelog(commits):
    if not commits:
        return {
            'changelog': 'Release update',
            'change_type': 'enhancement',
            'details': '',
            'full_changelog': '📦 Release update'
        }
    
    all_messages = ' '.join(commits)
    
    change_type = 'fix'
    for prefix, ctype in [('fix', 'fix'), ('feat', 'feature'), ('enhance', 'enhancement'),
                          ('perf', 'performance'), ('docs', 'documentation'), ('security', 'security')]:
        if prefix in all_messages.lower():
            change_type = ctype
            break
    
    changelog = commits[0] if commits else 'Update'
    if len(changelog) > 80:
        changelog = changelog[:77] + '...'
    
    details = '\n'.join(commits[1:]) if len(commits) > 1 else ''
    
    return {
        'changelog': changelog,
        'change_type': change_type,
        'details': details,
        'full_changelog': f"{get_icon(change_type)} {changelog}"
    }

def update_readme(changelog_info, update_docs):
    try:
        readme_path = BUILD_DIR.parent / 'README.md'
        version = get_current_version()
        
        results = update_docs.update_all_readme_docs(
            readme_path, version, changelog_info,
            {'changelog_limit': CONFIG['changelog_limit']}
        )
        
        if results.get('version_badge'):
            print("   ✅ README.md version badge updated")
        else:
            print("   ⚠️  Version badge update failed")
        
        if results.get('releases_table'):
            print("   ✅ README.md Releases table updated")
        else:
            print("   ⚠️  Releases table update failed")
        
        if results.get('changelog_section'):
            print(f"   ✅ README.md Changelog updated (limit: {CONFIG['changelog_limit']})")
        else:
            print("   ⚠️  Changelog section update failed")
        
        return all(results.values())
    except Exception as e:
        print(f"   ❌ README.md update failed: {e}")
        return False

def commit_readme_changes(version):
    try:
        subprocess.run(['git', 'add', 'README.md'], 
                      cwd=BUILD_DIR.parent, capture_output=True)
        result = subprocess.run(
            ['git', 'commit', '-m', f"docs: Update changelog for v{version}"],
            cwd=BUILD_DIR.parent, capture_output=True, text=True
        )
        if result.returncode == 0:
            print(f"   ✅ Committed: docs: Update changelog for v{version}")
            return True
        elif 'nothing to commit' in result.stdout:
            print("   ℹ️  No changes to commit")
            return True
        else:
            print(f"   ⚠️  Commit failed: {result.stderr}")
            return True
    except Exception as e:
        print(f"   ⚠️  Failed to commit: {e}")
        return True

def schedule_wiki_update(version, changelog_info):
    try:
        marker_file = BUILD_DIR / '.wiki-update-pending'
        with open(marker_file, 'w', encoding='utf-8') as f:
            json.dump({
                'version': version,
                'changelog_info': changelog_info,
                'timestamp': datetime.now().isoformat()
            }, f, indent=2)
        
        print(f"\n📚 Wiki update scheduled for after push...")
        print(f"   Version: v{version}")
        print(f"   Changelog: {changelog_info['full_changelog']}")
        print("")
        print("   Wiki will be updated automatically after push completes.")
        print("   Run manually if needed: python .build/hooks/process-wiki-update.py")
        return True
    except Exception as e:
        print(f"   ⚠️  Failed to schedule wiki update: {e}")
        return False

def handle_dev_push(commits, update_docs):
    print("\n" + "=" * 50)
    print("🔍 DEV branch push detected")
    print("=" * 50)
    
    version = get_current_version()
    print(f"📌 Current version: {version}")
    
    if check_skip_keyword(commits):
        print("⏭️  Skip keyword detected, skipping documentation update")
        return True
    
    changelog_info = prompt_changelog()
    
    if not changelog_info:
        return True
    
    print("\n📝 Updating README.md...")
    if update_readme(changelog_info, update_docs):
        commit_readme_changes(version)
    else:
        print("⚠️  README.md update failed, but push will continue")
        print("    Please manually update README.md later")
    
    return True

def handle_master_push(commits):
    print("\n" + "=" * 50)
    print("🔍 MASTER branch push detected")
    print("=" * 50)
    
    version = get_current_version()
    print(f"📌 Current version: {version}")
    
    if check_skip_keyword(commits):
        print("⏭️  Skip keyword detected, skipping wiki update")
        return True
    
    changelog_info = parse_commits_for_changelog(commits)
    
    print(f"\n📋 Parsed changelog from commits:")
    print(f"   {changelog_info['full_changelog']}")
    
    if len(commits) > 1:
        print(f"   ({len(commits)} commits parsed)")
    
    schedule_wiki_update(version, changelog_info)
    
    return True

def main():
    load_config()
    
    if not CONFIG.get('enabled', True):
        return 0
    
    update_docs = import_update_docs()
    if update_docs is None:
        print("⚠️  update_docs module not found, skipping documentation update")
        return 0
    
    pushes = get_push_info()
    
    if not pushes:
        return 0
    
    for push in pushes:
        branch = push['branch']
        commits = get_commits_being_pushed(push['local_sha'], push['remote_sha'])
        
        if branch == CONFIG['dev_branch']:
            if CONFIG.get('dev_update_readme', True):
                handle_dev_push(commits, update_docs)
        elif branch == CONFIG['master_branch']:
            if CONFIG.get('master_update_wiki', True):
                handle_master_push(commits)
    
    return 0

if __name__ == '__main__':
    sys.exit(main())
