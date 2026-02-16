#!/usr/bin/env python3
"""
Cart Quote WooCommerce & Email - Automated Deployment System
Single command to deploy changes from dev → master → release

Author: Jerel Yoshida
Version: 1.0.0

Usage:
    python deploy.py                    # Full deployment
    python deploy.py --dry-run          # Preview only
    python deploy.py --no-wiki          # Skip wiki update
    python deploy.py --no-release       # Skip GitHub release
    python deploy.py --dev-only         # Push to dev only
    python deploy.py --docs-only        # Update docs only
"""

import os
import sys
import json
import subprocess
import argparse
import shutil
from pathlib import Path
from datetime import datetime
import importlib.util

_build_dir = Path(__file__).parent
_update_docs_path = _build_dir / 'update_docs.py'

_spec = importlib.util.spec_from_file_location('update_docs', _update_docs_path)
update_docs = importlib.util.module_from_spec(_spec)
sys.modules['update_docs'] = update_docs
_spec.loader.exec_module(update_docs)

update_all_readme_docs = update_docs.update_all_readme_docs
sync_wiki_changelog = update_docs.sync_wiki_changelog
DocumentationUpdater = update_docs.DocumentationUpdater

if sys.platform == 'win32':
    import codecs
    try:
        if hasattr(sys.stdout, 'buffer'):
            sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')
        if hasattr(sys.stderr, 'buffer'):
            sys.stderr = codecs.getwriter('utf-8')(sys.stderr.buffer, 'strict')
    except Exception:
        pass

# ANSI color codes for terminal output
class Colors:
    HEADER = '\033[95m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    GREEN = '\033[92m'
    YELLOW = '\033[93m'
    RED = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'
    UNDERLINE = '\033[4m'

def print_header(text):
    """Print colored header"""
    print(f"\n{Colors.BOLD}{Colors.CYAN}{text}{Colors.ENDC}")
    print("═" * len(text))

def print_step(step, total, text):
    """Print step progress"""
    print(f"\n{Colors.BOLD}[{step}/{total}]{Colors.ENDC} {Colors.BLUE}{text}{Colors.ENDC}")

def print_success(text):
    """Print success message"""
    print(f"       {Colors.GREEN}✅ {text}{Colors.ENDC}")

def print_error(text):
    """Print error message"""
    print(f"       {Colors.RED}❌ {text}{Colors.ENDC}")

def print_warning(text):
    """Print warning message"""
    print(f"       {Colors.YELLOW}⚠️  {text}{Colors.ENDC}")

def print_info(text):
    """Print info message"""
    print(f"       {text}")

def run_command(cmd, cwd=None, capture_output=True):
    """Run shell command and return result"""
    try:
        result = subprocess.run(
            cmd,
            shell=True,
            cwd=cwd,
            capture_output=capture_output,
            text=True,
            encoding='utf-8'
        )
        return result
    except Exception as e:
        print_error(f"Command failed: {cmd}")
        print_error(f"Error: {str(e)}")
        return None

def load_config():
    """Load deployment configuration"""
    build_dir = Path(__file__).parent
    config_path = build_dir / 'deploy-config.json'
    
    if config_path.exists():
        with open(config_path, 'r', encoding='utf-8') as f:
            return json.load(f)
    else:
        # Return default config
        return {
            "repository": {
                "owner": "jerelryoshida-dot",
                "name": "cart-quote-woocommerce-email",
                "dev_branch": "dev",
                "master_branch": "master"
            },
            "version": {
                "auto_increment": True,
                "increment_type": "patch"
            },
            "build": {
                "create_zip": True,
                "validate_zip": True
            },
            "documentation": {
                "update_readme": True,
                "update_wiki": True
            },
            "release": {
                "create_github_release": True,
                "attach_zip": True
            }
        }

def get_current_version():
    """Read current version from main plugin file"""
    build_dir = Path(__file__).parent
    plugin_file = build_dir.parent / 'cart-quote-woocommerce-email.php'
    
    with open(plugin_file, 'r', encoding='utf-8') as f:
        for line in f:
            if line.strip().startswith('* Version:'):
                version = line.split(':')[1].strip()
                return version
    
    return None

def increment_version(version):
    """Increment patch version (1.0.16 → 1.0.17)"""
    parts = version.split('.')
    parts[-1] = str(int(parts[-1]) + 1)
    return '.'.join(parts)

def get_change_type_icon(change_type):
    """Get emoji icon for change type"""
    icons = {
        'fix': '🐛',
        'feature': '✨',
        'enhancement': '🔧',
        'performance': '🚀',
        'documentation': '📝',
        'security': '🔒'
    }
    return icons.get(change_type, '📦')

def interactive_prompts(interactive=True):
    """Get deployment information from user"""
    print_header("📋 Deployment Information")

    # Get changelog description
    if interactive:
        print(f"\n{Colors.BOLD}❓ What changed in this release?{Colors.ENDC}")
        changelog = input("   > ").strip()
    else:
        print_warning("DRY-RUN or NON-INTERACTIVE MODE")
        print_warning("Using default changelog for demonstration")
        changelog = "Test deployment with SSH authentication"

    if not changelog:
        print_error("Changelog description is required!")
        sys.exit(1)

    # Get change type
    if interactive:
        print(f"\n{Colors.BOLD}❓ Change type:{Colors.ENDC}")
        print("   [1] 🐛 fix       - Bug fixes")
        print("   [2] ✨ feature   - New features")
        print("   [3] 🔧 enhancement - Improvements")
        print("   [4] 🚀 performance - Optimizations")
        print("   [5] 📝 documentation - Docs updates")
        print("   [6] 🔒 security  - Security fixes")

        type_choice = input("   > ").strip()
    else:
        type_choice = '1'  # Default to fix for non-interactive

    type_map = {
        '1': 'fix',
        '2': 'feature',
        '3': 'enhancement',
        '4': 'performance',
        '5': 'documentation',
        '6': 'security'
    }

    change_type = type_map.get(type_choice, 'fix')

    # Get additional details
    if interactive:
        print(f"\n{Colors.BOLD}❓ Additional details (optional, press Enter to skip):{Colors.ENDC}")
        details = input("   > ").strip()
    else:
        details = ""

    # Build full changelog entry
    icon = get_change_type_icon(change_type)
    full_changelog = f"{icon} {changelog}"

    if details:
        full_changelog += f": {details}"

    return {
        'changelog': changelog,
        'change_type': change_type,
        'details': details,
        'full_changelog': full_changelog
    }

def validate_environment():
    """Validate required tools and environment"""
    print_step(1, 10, "🔍 Validating environment...")
    
    build_dir = Path(__file__).parent
    plugin_dir = build_dir.parent
    
    # Check git
    result = run_command('git --version')
    if result and result.returncode == 0:
        print_success("Git available")
    else:
        print_error("Git not found!")
        return False
    
    # Check GitHub CLI
    result = run_command('gh --version')
    if result and result.returncode == 0:
        print_success("GitHub CLI available")
    else:
        print_error("GitHub CLI not found!")
        return False
    
    # Check Python version
    python_version = f"{sys.version_info.major}.{sys.version_info.minor}.{sys.version_info.micro}"
    print_success(f"Python {python_version} detected")
    
    # Check current branch
    result = run_command('git branch --show-current', cwd=plugin_dir)
    if result and result.returncode == 0:
        current_branch = result.stdout.strip()
        print_success(f"Current branch: {current_branch}")
    
    # Check if working tree is clean
    result = run_command('git status --porcelain', cwd=plugin_dir)
    if result and result.returncode == 0:
        if result.stdout.strip():
            print_warning("Working tree has uncommitted changes")
            print_info("These will be committed as part of deployment")
        else:
            print_success("Working tree is clean")
    
    return True

def update_versions(new_version, dry_run=False):
    """Update version in all files"""
    print_step(3, 10, f"🔢 Updating versions to {new_version}...")
    
    build_dir = Path(__file__).parent
    plugin_dir = build_dir.parent
    
    files_to_update = [
        {
            'path': plugin_dir / 'cart-quote-woocommerce-email.php',
            'updates': [
                {'pattern': r'\* Version:\s+[\d.]+', 'replacement': f'* Version: {new_version}'},
                {'pattern': r"define\('CART_QUOTE_WC_VERSION',\s*'[\d.]+'\);", 
                 'replacement': f"define('CART_QUOTE_WC_VERSION', '{new_version}');"}
            ]
        },
        {
            'path': plugin_dir / 'src' / 'Core' / 'Plugin.php',
            'updates': [
                {'pattern': r"private \$version\s*=\s*'[\d.]+';", 
                 'replacement': f"private $version = '{new_version}';"}
            ]
        },
        {
            'path': Path('D:/Projects/cart-quote-dev-tools/tests/phpunit/bootstrap.php'),
            'updates': [
                {'pattern': r"define\('CART_QUOTE_WC_VERSION',\s*'[\d.]+'\);", 
                 'replacement': f"define('CART_QUOTE_WC_VERSION', '{new_version}');"}
            ]
        }
    ]
    
    if dry_run:
        for file_info in files_to_update:
            print_info(f"Would update: {file_info['path'].name}")
        return True
    
    # Update files using Python instead of sed
    import re
    
    for file_info in files_to_update:
        file_path = file_info['path']
        
        if not file_path.exists():
            print_warning(f"File not found: {file_path}")
            continue
        
        # Read file content
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Apply updates
        for update in file_info['updates']:
            content = re.sub(update['pattern'], update['replacement'], content)
        
        # Write updated content
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)
        
        print_success(f"{file_path.name} updated")
    
    return True

def build_plugin_zip(version, dry_run=False):
    """Build plugin ZIP file"""
    print_step(4, 10, "📦 Building plugin ZIP...")
    
    build_dir = Path(__file__).parent
    
    if dry_run:
        print_info(f"Would build: cart-quote-woocommerce-email-v{version}.zip")
        return True
    
    # Run build-zip.py
    cmd = f'python build-zip.py {version}'
    result = run_command(cmd, cwd=build_dir, capture_output=True)
    
    if result and result.returncode == 0:
        print_success(f"ZIP created: cart-quote-woocommerce-email-v{version}.zip")
        
        # Show validation output
        if 'PASS' in result.stdout:
            print_success("Validation passed")
        
        return True
    else:
        print_error("Failed to build ZIP")
        if result:
            print_error(result.stderr)
        return False

def update_readme(version, changelog_info, dry_run=False, config=None):
    """Update README.md with new release"""
    print_step(5, 10, "📄 Updating documentation...")
    
    build_dir = Path(__file__).parent
    readme_path = build_dir.parent / 'README.md'
    
    if dry_run:
        print_info("Would update README.md version badge")
        print_info("Would update README.md Releases table")
        print_info("Would update README.md Changelog section (limit: 5)")
        return True
    
    doc_config = {'changelog_limit': 5}
    if config and 'documentation' in config:
        doc_config['changelog_limit'] = config['documentation'].get('changelog_limit', 5)
    
    results = update_all_readme_docs(readme_path, version, changelog_info, doc_config)
    
    if results['version_badge']:
        print_success("README.md version badge updated")
    else:
        print_warning("Failed to update version badge")
    
    if results['releases_table']:
        print_success("README.md Releases table updated")
    else:
        print_warning("Failed to update Releases table")
    
    if results['changelog_section']:
        print_success(f"README.md Changelog updated (limit: {doc_config['changelog_limit']})")
    else:
        print_warning("Failed to update Changelog section")
    
    return all(results.values())

def git_commit_and_push(version, message, branch, dry_run=False):
    """Commit changes and push to branch"""
    print_step(6, 10, f"💾 Committing and pushing to {branch}...")
    
    build_dir = Path(__file__).parent
    plugin_dir = build_dir.parent
    
    if dry_run:
        print_info(f"Would commit with message: v{version}: {message}")
        print_info(f"Would push to origin/{branch}")
        return True
    
    # Add all changes
    result = run_command('git add .', cwd=plugin_dir)
    if result.returncode != 0:
        print_error("Failed to stage files")
        return False
    
    # Commit
    commit_message = f"v{version}: {message}"
    result = run_command(f'git commit -m "{commit_message}"', cwd=plugin_dir)
    
    if result.returncode != 0:
        # Check if there's nothing to commit
        if 'nothing to commit' in result.stdout:
            print_warning("No changes to commit")
        else:
            print_error("Failed to commit")
            return False
    else:
        print_success(f"Committed: {commit_message}")
    
    # Push to branch
    result = run_command(f'git push origin {branch}', cwd=plugin_dir)
    
    if result.returncode == 0:
        print_success(f"Pushed to origin/{branch}")
        return True
    else:
        print_error(f"Failed to push to {branch}")
        return False

def merge_to_master(config, version, dry_run=False):
    """Merge dev to master and create git tag"""
    print_step(7, 10, "🔀 Merging dev → master...")
    
    build_dir = Path(__file__).parent
    plugin_dir = build_dir.parent
    
    dev_branch = config['repository']['dev_branch']
    master_branch = config['repository']['master_branch']
    
    if dry_run:
        print_info(f"Would checkout {master_branch}")
        print_info(f"Would merge {dev_branch} → {master_branch}")
        print_info(f"Would create tag v{version}")
        print_info(f"Would push to origin/{master_branch} with tags")
        return True
    
    # Checkout master
    result = run_command(f'git checkout {master_branch}', cwd=plugin_dir)
    if result.returncode != 0:
        print_error(f"Failed to checkout {master_branch}")
        return False
    
    print_success(f"Checked out {master_branch}")
    
    # Merge dev
    result = run_command(f'git merge {dev_branch} --no-ff -m "Merge {dev_branch} into {master_branch}"', 
                        cwd=plugin_dir)
    
    if result.returncode != 0:
        print_error(f"Failed to merge {dev_branch}")
        return False
    
    print_success(f"Merged {dev_branch} → {master_branch}")
    
    # Create git tag
    result = run_command(f'git tag -a v{version} -m "Release v{version}"', cwd=plugin_dir)
    if result.returncode == 0:
        print_success(f"Created tag v{version}")
    else:
        print_warning(f"Tag v{version} may already exist or failed to create")
    
    # Push to master with tags
    result = run_command(f'git push origin {master_branch} --follow-tags', cwd=plugin_dir)
    
    if result.returncode == 0:
        print_success(f"Pushed to origin/{master_branch} with tags")
        return True
    else:
        print_error(f"Failed to push to {master_branch}")
        return False

def create_github_release(version, changelog_info, dry_run=False):
    """Create GitHub release"""
    print_step(8, 10, "🎉 Creating GitHub release...")
    
    build_dir = Path(__file__).parent
    plugin_dir = build_dir.parent
    
    zip_file = build_dir / 'output' / f'cart-quote-woocommerce-email-v{version}.zip'
    
    if dry_run:
        print_info(f"Would create release: v{version}")
        print_info(f"Would attach: {zip_file.name}")
        return True
    
    # Create release notes
    release_notes = f"## {changelog_info['full_changelog']}\n\n"
    
    if changelog_info['details']:
        release_notes += f"{changelog_info['details']}\n\n"
    
    release_notes += f"### Installation\n"
    release_notes += f"1. Download the zip file below\n"
    release_notes += f"2. Go to WordPress Admin > Plugins > Add New > Upload Plugin\n"
    release_notes += f"3. Activate the plugin\n"
    
    # Save release notes to file
    notes_file = build_dir / f'release-notes-v{version}.md'
    with open(notes_file, 'w', encoding='utf-8') as f:
        f.write(release_notes)
    
    # Create release using gh CLI
    title = f"v{version} - {changelog_info['changelog']}"
    
    cmd = f'gh release create v{version} --title "{title}" --notes-file "{notes_file}" "{zip_file}"'
    result = run_command(cmd, cwd=plugin_dir)
    
    if result and result.returncode == 0:
        print_success(f"Release v{version} created")
        print_success("ZIP attached to release")
        return True
    else:
        print_error("Failed to create release")
        if result:
            print_error(result.stderr)
        return False

def update_wiki(version, changelog_info, dry_run=False, config=None):
    """Update GitHub wiki with full changelog sync"""
    print_step(9, 10, "📚 Updating wiki...")
    
    build_dir = Path(__file__).parent
    readme_path = build_dir.parent / 'README.md'
    
    wiki_path = build_dir.parent.parent / 'cart-quote-woocommerce-email.wiki'
    if config and 'documentation' in config:
        wiki_path_str = config['documentation'].get('wiki_path', '')
        if wiki_path_str:
            wiki_path = Path(wiki_path_str)
    
    if dry_run:
        print_info("Would clone/pull wiki")
        print_info("Would update Update-Log.md Release History table")
        print_info("Would sync full Changelog from README.md to Wiki")
        print_info("Would push wiki changes")
        return True
    
    if not wiki_path.exists():
        print_info("Cloning wiki...")
        result = run_command(
            'gh repo clone jerelryoshida-dot/cart-quote-woocommerce-email.wiki cart-quote-woocommerce-email.wiki',
            cwd=build_dir.parent.parent
        )
        
        if result.returncode != 0:
            print_error("Failed to clone wiki")
            return False
        
        print_success("Wiki cloned")
    else:
        run_command('git pull', cwd=wiki_path)
        print_success("Wiki pulled")
    
    updatelog_path = wiki_path / 'Update-Log.md'
    
    if not updatelog_path.exists():
        print_warning("Update-Log.md not found in wiki")
        return False
    
    if sync_wiki_changelog(wiki_path, readme_path, version, changelog_info):
        print_success("Update-Log.md Release History updated")
        print_success("Full Changelog synced from README.md")
        
        run_command('git add .', cwd=wiki_path)
        run_command(f'git commit -m "Update changelog for v{version}"', cwd=wiki_path)
        result = run_command('git push', cwd=wiki_path)
        
        if result.returncode == 0:
            print_success("Wiki pushed")
        else:
            print_warning("Failed to push wiki (manual push may be needed)")
    else:
        print_error("Failed to sync wiki changelog")
        return False
    
    return True

def cleanup(version, dry_run=False):
    """Cleanup temporary files"""
    print_step(10, 10, "🧹 Cleaning up...")
    
    build_dir = Path(__file__).parent
    
    files_to_delete = [
        build_dir / f'release-notes-v{version}.md'
    ]
    
    if dry_run:
        for file_path in files_to_delete:
            if file_path.exists():
                print_info(f"Would delete: {file_path.name}")
        return True
    
    for file_path in files_to_delete:
        if file_path.exists():
            file_path.unlink()
            print_success(f"Deleted {file_path.name}")
    
    return True

def main():
    """Main deployment workflow"""
    parser = argparse.ArgumentParser(description='Automated deployment for Cart Quote plugin')
    parser.add_argument('--dry-run', action='store_true', help='Preview changes without executing')
    parser.add_argument('--no-wiki', action='store_true', help='Skip wiki update')
    parser.add_argument('--no-release', action='store_true', help='Skip GitHub release creation')
    parser.add_argument('--dev-only', action='store_true', help='Push to dev branch only')
    parser.add_argument('--docs-only', action='store_true', help='Update documentation only')
    
    args = parser.parse_args()
    
    # Print header
    print_header("🚀 Cart Quote WooCommerce - Automated Deployment")
    
    if args.dry_run:
        print_warning("DRY-RUN MODE - No changes will be made\n")
    
    # Load configuration
    config = load_config()
    
    # Validate environment
    if not validate_environment():
        print_error("\nEnvironment validation failed!")
        sys.exit(1)
    
    # Get current and new version
    current_version = get_current_version()
    new_version = increment_version(current_version)
    
    print_step(2, 10, "📝 Gathering information...")
    print_info(f"Current version: {current_version}")
    print_info(f"New version: {new_version}")
    
    # Get changelog info from user (non-interactive in dry-run/docs mode)
    changelog_info = interactive_prompts(interactive=(not args.dry_run and not args.docs_only))
    
    print(f"\n{Colors.GREEN}✅ Changelog generated:{Colors.ENDC}")
    print(f'   "{changelog_info["full_changelog"]}"')
    
    # Show deployment plan
    changelog_limit = config.get('documentation', {}).get('changelog_limit', 5)
    print_header("🔄 Deployment Plan")
    print(f"   ├─ Update version: {current_version} → {new_version}")
    print(f"   ├─ Build ZIP: cart-quote-woocommerce-email-v{new_version}.zip")
    print(f"   ├─ Update README.md:")
    print(f"   │   ├─ Version badge")
    print(f"   │   ├─ Releases table")
    print(f"   │   └─ Changelog section (limit: {changelog_limit})")
    print(f"   ├─ Commit & push to dev")
    
    if not args.dev_only:
        print(f"   ├─ Merge dev → master")
        
        if not args.no_release:
            print(f"   ├─ Create GitHub release v{new_version}")
        
        if not args.no_wiki:
            print(f"   ├─ Update Wiki Update-Log:")
            print(f"   │   ├─ Release History table")
            print(f"   │   └─ Full Changelog sync")
    
    print(f"   └─ Cleanup temp files")
    
    if not args.dry_run and not args.docs_only:
        print(f"\n{Colors.YELLOW}⚠️  This will push changes to GitHub.{Colors.ENDC}")
        confirm = input(f"{Colors.BOLD}Continue? (y/n):{Colors.ENDC} ").strip().lower()
        
        if confirm != 'y':
            print("\n❌ Deployment cancelled")
            sys.exit(0)
    
    print_header("🚀 Starting deployment...")
    
    # Execute deployment steps
    try:
        # Update versions
        if not update_versions(new_version, args.dry_run):
            raise Exception("Version update failed")
        
        # Build ZIP
        if not args.docs_only:
            if not build_plugin_zip(new_version, args.dry_run):
                raise Exception("ZIP build failed")
        
        # Update README
        if not update_readme(new_version, changelog_info, args.dry_run, config):
            raise Exception("README update failed")
        
        # Git workflow
        if not args.docs_only:
            # Push to dev
            dev_branch = config['repository']['dev_branch']
            if not git_commit_and_push(new_version, changelog_info['changelog'], dev_branch, args.dry_run):
                raise Exception("Push to dev failed")
            
            # Merge to master (unless dev-only)
            if not args.dev_only:
                if not merge_to_master(config, new_version, args.dry_run):
                    raise Exception("Merge to master failed")
                
                # Create release
                if not args.no_release:
                    if not create_github_release(new_version, changelog_info, args.dry_run):
                        raise Exception("Release creation failed")
                
                # Update wiki
                if not args.no_wiki:
                    if not update_wiki(new_version, changelog_info, args.dry_run, config):
                        print_warning("Wiki update failed (non-critical)")
        
        # Cleanup
        cleanup(new_version, args.dry_run)
        
        # Success summary
        print_header("✅ DEPLOYMENT SUCCESSFUL!")
        
        print(f"\n{Colors.BOLD}📊 Summary:{Colors.ENDC}")
        print(f"   Version: {current_version} → {new_version}")
        print(f"   Branch: dev → master")
        
        if not args.no_release and not args.dev_only:
            print(f"   Release: v{new_version}")
        
        if not args.no_wiki and not args.dev_only:
            print(f"   Wiki: Updated")
        
        print(f"\n{Colors.BOLD}🔗 Links:{Colors.ENDC}")
        
        if not args.no_release and not args.dev_only:
            print(f"   Release: https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v{new_version}")
        
        if not args.no_wiki and not args.dev_only:
            print(f"   Wiki: https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/wiki/Update-Log")
        
    except Exception as e:
        print_header("❌ DEPLOYMENT FAILED")
        print_error(f"Error: {str(e)}")
        print_info("\nPlease fix the issue and try again.")
        sys.exit(1)

if __name__ == '__main__':
    main()
