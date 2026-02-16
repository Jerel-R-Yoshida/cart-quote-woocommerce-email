#!/usr/bin/env python3
"""
Wiki Update Helper

Standalone script to update GitHub wiki without running full build.
Syncs full changelog from README.md to Wiki Update-Log.md

Usage:
    python update-wiki.py --version "1.0.25" --changelog "Security fixes" --type "security"
    python update-wiki.py --version "1.0.25" --sync-only  # Just sync, don't add new entry
"""

import subprocess
import sys
import argparse
from pathlib import Path
import importlib.util

_build_dir = Path(__file__).parent
_update_docs_path = _build_dir / 'update_docs.py'

_spec = importlib.util.spec_from_file_location('update_docs', _update_docs_path)
update_docs = importlib.util.module_from_spec(_spec)
sys.modules['update_docs'] = update_docs
_spec.loader.exec_module(update_docs)

sync_wiki_changelog = update_docs.sync_wiki_changelog
extract_full_changelog = update_docs.extract_full_changelog
DocumentationUpdater = update_docs.DocumentationUpdater


def clone_wiki(wiki_path):
    """Clone wiki repository if not exists"""
    if wiki_path.exists():
        print(f"✓ Wiki already cloned at: {wiki_path}")
        result = subprocess.run(
            ['git', 'pull'],
            cwd=wiki_path,
            capture_output=True,
            text=True
        )
        if result.returncode == 0:
            print("✓ Wiki pulled latest changes")
        return True

    print(f"Cloning wiki to: {wiki_path}")
    result = subprocess.run(
        ['gh', 'repo', 'clone', 'jerelryoshida-dot/cart-quote-woocommerce-email.wiki', str(wiki_path)],
        capture_output=True,
        text=True
    )

    if result.returncode == 0:
        print("✓ Wiki cloned successfully")
        return True
    else:
        print(f"✗ Failed to clone wiki: {result.stderr}")
        return False


def commit_and_push_wiki(wiki_path, version, auto_push=False):
    """Commit and optionally push wiki changes"""
    result = subprocess.run(
        ['git', 'add', '.'],
        cwd=wiki_path,
        capture_output=True,
        text=True
    )
    
    result = subprocess.run(
        ['git', 'commit', '-m', f"Update changelog for v{version}"],
        cwd=wiki_path,
        capture_output=True,
        text=True
    )
    
    if result.returncode == 0:
        print(f"✓ Committed changes for v{version}")
    else:
        print("⚠ No changes to commit")
        return True
    
    if auto_push:
        result = subprocess.run(
            ['git', 'push'],
            cwd=wiki_path,
            capture_output=True,
            text=True
        )
        
        if result.returncode == 0:
            print("✓ Wiki pushed successfully")
            return True
        else:
            print(f"✗ Failed to push wiki: {result.stderr}")
            return False
    else:
        show_push_instructions(wiki_path, version)
        return True


def show_push_instructions(wiki_path, version):
    """Show push instructions"""
    print("\n" + "=" * 50)
    print("NEXT STEPS:")
    print("=" * 50)
    print("\nTo push wiki updates, run:")
    print(f"  cd {wiki_path}")
    print(f'  git commit -m "Update changelog for v{version}"')
    print("  git push origin master")
    print("\n" + "=" * 50)


def main():
    parser = argparse.ArgumentParser(description='Update GitHub wiki with release information')
    parser.add_argument('--version', required=True, help='Version number (e.g., 1.0.25)')
    parser.add_argument('--changelog', help='Brief changelog message')
    parser.add_argument('--type', default='fix',
                        choices=['fix', 'feature', 'enhancement', 'performance', 'documentation', 'security'],
                        help='Type of change')
    parser.add_argument('--details', help='Detailed changes (multiline string)')
    parser.add_argument('--wiki-path', default='D:/Projects/cart-quote-woocommerce-email.wiki',
                        help='Path to wiki repository')
    parser.add_argument('--readme-path', default=None,
                        help='Path to README.md (default: ../README.md relative to this script)')
    parser.add_argument('--no-clone', action='store_true',
                        help='Skip cloning if wiki already exists')
    parser.add_argument('--sync-only', action='store_true',
                        help='Only sync changelog from README.md, do not add new entry')
    parser.add_argument('--push', action='store_true',
                        help='Automatically push changes to wiki')
    
    args = parser.parse_args()
    
    wiki_path = Path(args.wiki_path)
    
    if args.readme_path:
        readme_path = Path(args.readme_path)
    else:
        readme_path = Path(__file__).parent.parent / 'README.md'
    
    if not readme_path.exists():
        print(f"✗ README.md not found at: {readme_path}")
        sys.exit(1)
    
    if not args.no_clone:
        if not clone_wiki(wiki_path):
            sys.exit(1)
    
    updatelog_path = wiki_path / 'Update-Log.md'
    if not updatelog_path.exists():
        print(f"✗ Update-Log.md not found at: {updatelog_path}")
        sys.exit(1)
    
    if args.sync_only:
        print("\n📄 Syncing full changelog from README.md to Wiki...")
        
        full_changelog = extract_full_changelog(readme_path)
        if not full_changelog:
            print("✗ No changelog found in README.md")
            sys.exit(1)
        
        print(f"✓ Extracted changelog ({len(full_changelog)} characters)")
        
        changelog_info = {
            'changelog': 'Sync from README.md',
            'change_type': args.type,
            'details': '',
            'full_changelog': '📝 Synced from README.md'
        }
        
        if sync_wiki_changelog(wiki_path, readme_path, args.version, changelog_info):
            print("✓ Wiki changelog synced successfully")
            commit_and_push_wiki(wiki_path, args.version, args.push)
        else:
            print("✗ Failed to sync wiki changelog")
            sys.exit(1)
    else:
        if not args.changelog:
            print("✗ --changelog is required when not using --sync-only")
            sys.exit(1)
        
        print(f"\n📄 Updating wiki for v{args.version}...")
        
        changelog_info = {
            'changelog': args.changelog,
            'change_type': args.type,
            'details': args.details or '',
            'full_changelog': f"{DocumentationUpdater.CHANGE_TYPE_ICONS.get(args.type, '📦')} {args.changelog}"
        }
        
        if sync_wiki_changelog(wiki_path, readme_path, args.version, changelog_info):
            print("✓ Wiki Release History updated")
            print("✓ Full Changelog synced from README.md")
            commit_and_push_wiki(wiki_path, args.version, args.push)
        else:
            print("✗ Failed to update wiki")
            sys.exit(1)
    
    print("\n✅ Wiki update complete!")


if __name__ == '__main__':
    main()
