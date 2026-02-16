#!/usr/bin/env python3
"""
Process Pending Wiki Updates

Called after push completes to sync Wiki with README.md changelog.
Triggered by:
  1. Git post-receive hook (automatic)
  2. Manual execution: python process-wiki-update.py

Author: Jerel Yoshida
Version: 1.0.0
"""

import sys
import json
import subprocess
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
CONFIG_PATH = BUILD_DIR / 'hook-config.json'
PENDING_FILE = BUILD_DIR / '.wiki-update-pending'

def load_config():
    if CONFIG_PATH.exists():
        with open(CONFIG_PATH, 'r', encoding='utf-8') as f:
            return json.load(f)
    return {
        'wiki_path': 'D:/Projects/cart-quote-woocommerce-email.wiki',
        'auto_push_wiki': True
    }

def import_update_docs():
    update_docs_path = BUILD_DIR / 'update_docs.py'
    if not update_docs_path.exists():
        print("❌ update_docs.py not found")
        return None
    
    import importlib.util
    spec = importlib.util.spec_from_file_location('update_docs', update_docs_path)
    if spec is None or spec.loader is None:
        return None
    update_docs = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(update_docs)
    return update_docs

def clone_or_pull_wiki(wiki_path):
    """Clone wiki if not exists, otherwise pull latest"""
    if wiki_path.exists():
        print("   📥 Pulling latest wiki changes...")
        result = subprocess.run(
            ['git', 'pull'],
            cwd=wiki_path,
            capture_output=True,
            text=True
        )
        if result.returncode == 0:
            print("   ✅ Wiki pulled successfully")
            return True
        else:
            print(f"   ⚠️  Pull failed: {result.stderr}")
            return True  # Continue anyway
    else:
        print(f"   📥 Cloning wiki to {wiki_path}...")
        result = subprocess.run(
            ['gh', 'repo', 'clone',
             'jerelryoshida-dot/cart-quote-woocommerce-email.wiki',
             str(wiki_path)],
            cwd=wiki_path.parent,
            capture_output=True,
            text=True
        )
        if result.returncode == 0:
            print("   ✅ Wiki cloned successfully")
            return True
        else:
            print(f"   ❌ Clone failed: {result.stderr}")
            return False

def push_wiki_changes(wiki_path, version):
    """Commit and push wiki changes"""
    subprocess.run(['git', 'add', '.'], cwd=wiki_path, capture_output=True)
    
    result = subprocess.run(
        ['git', 'commit', '-m', f"Update changelog for v{version}"],
        cwd=wiki_path,
        capture_output=True,
        text=True
    )
    
    if result.returncode != 0:
        if 'nothing to commit' in result.stdout:
            print("   ℹ️  No changes to commit")
            return True
        print(f"   ⚠️  Commit failed: {result.stderr}")
        return False
    
    print(f"   ✅ Committed: Update changelog for v{version}")
    
    result = subprocess.run(
        ['git', 'push'],
        cwd=wiki_path,
        capture_output=True,
        text=True
    )
    
    if result.returncode == 0:
        print("   ✅ Wiki pushed successfully")
        return True
    else:
        print(f"   ⚠️  Push failed: {result.stderr}")
        print("   Manual push needed: cd wiki && git push")
        return False

def main():
    print("\n" + "=" * 50)
    print("📚 WIKI UPDATE PROCESSOR")
    print("=" * 50)
    
    if not PENDING_FILE.exists():
        print("\nℹ️  No pending wiki updates")
        return 0
    
    update_docs = import_update_docs()
    if update_docs is None:
        return 1
    
    with open(PENDING_FILE, 'r', encoding='utf-8') as f:
        pending = json.load(f)
    
    version = pending['version']
    changelog_info = pending['changelog_info']
    timestamp = pending.get('timestamp', 'unknown')
    
    print(f"\n📋 Pending update from: {timestamp}")
    print(f"📌 Version: v{version}")
    print(f"📝 Changelog: {changelog_info['full_changelog']}")
    
    config = load_config()
    wiki_path = Path(config.get('wiki_path', 'D:/Projects/cart-quote-woocommerce-email.wiki'))
    readme_path = BUILD_DIR.parent / 'README.md'
    
    print(f"\n📚 Updating Wiki...")
    
    if not clone_or_pull_wiki(wiki_path):
        return 1
    
    updatelog_path = wiki_path / 'Update-Log.md'
    if not updatelog_path.exists():
        print(f"   ❌ Update-Log.md not found in wiki")
        return 1
    
    print("   📄 Syncing changelog from README.md...")
    
    if update_docs.sync_wiki_changelog(wiki_path, readme_path, version, changelog_info):
        print("   ✅ Wiki Release History updated")
        print("   ✅ Wiki Changelog synced from README.md")
        
        if config.get('auto_push_wiki', True):
            push_wiki_changes(wiki_path, version)
        else:
            print("   ℹ️  Auto-push disabled, commit manually")
    else:
        print("   ❌ Wiki sync failed")
        return 1
    
    PENDING_FILE.unlink()
    print("\n✅ Wiki update complete!")
    
    return 0

if __name__ == '__main__':
    sys.exit(main())
