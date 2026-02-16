#!/usr/bin/env python3
"""
Documentation Update Module for Cart Quote WooCommerce & Email

Centralized module for updating README.md and Wiki documentation.
Handles version badges, releases tables, and changelog synchronization.

Author: Jerel Yoshida
Version: 1.0.0

Usage:
    from update_docs import (
        update_readme_version_badge,
        update_readme_releases_table,
        update_readme_changelog_section,
        extract_full_changelog,
        sync_wiki_changelog
    )
"""

import re
import sys
from pathlib import Path
from datetime import datetime
from typing import Optional, Dict, List, Tuple

if sys.platform == 'win32':
    import codecs
    try:
        if hasattr(sys.stdout, 'buffer'):
            sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')
        if hasattr(sys.stderr, 'buffer'):
            sys.stderr = codecs.getwriter('utf-8')(sys.stderr.buffer, 'strict')
    except Exception:
        pass


class DocumentationUpdater:
    """Handles all documentation update operations"""
    
    REPO_URL = "https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email"
    WIKI_URL = "https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/wiki/Update-Log"
    
    CHANGE_TYPE_ICONS = {
        'fix': '🐛',
        'feature': '✨',
        'enhancement': '🔧',
        'performance': '🚀',
        'documentation': '📝',
        'security': '🔒'
    }
    
    CHANGE_TYPE_LABELS = {
        'fix': 'Bug Fixes',
        'feature': 'New Features',
        'enhancement': 'Enhancements',
        'performance': 'Performance',
        'documentation': 'Documentation',
        'security': 'Security Fixes'
    }
    
    def __init__(self, readme_path: Path, config: Dict = None):
        self.readme_path = Path(readme_path)
        self.config = config or {}
        self.changelog_limit = self.config.get('changelog_limit', 5)
    
    def update_version_badge(self, version: str) -> bool:
        """Update version badge in README.md (line 3)"""
        try:
            with open(self.readme_path, 'r', encoding='utf-8') as f:
                lines = f.readlines()
            
            for i, line in enumerate(lines):
                if 'badge/version-' in line:
                    match = re.search(r'version-([\d.]+)-', line)
                    if match:
                        old_version = match.group(1)
                        lines[i] = line.replace(f'version-{old_version}-', f'version-{version}-')
                        break
            
            with open(self.readme_path, 'w', encoding='utf-8') as f:
                f.writelines(lines)
            
            return True
        except Exception as e:
            print(f"Error updating version badge: {e}")
            return False
    
    def update_releases_table(self, version: str, changelog_info: Dict) -> bool:
        """
        Add entry to Releases table in README.md
        - Add new version at top
        - Trim to keep only N newest versions
        - Add "See Releases" link at bottom if trimmed
        """
        try:
            with open(self.readme_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            today = datetime.now().strftime('%Y-%m-%d')
            repo_url = self.REPO_URL
            
            new_row = f"| [{version}]({repo_url}/releases/tag/v{version}) | {today} | {changelog_info['full_changelog']} |"
            
            lines = content.split('\n')
            table_start_idx = -1
            separator_idx = -1
            
            for i, line in enumerate(lines):
                if '|---------|------|---------|' in line and i > 0:
                    if 'Version' in lines[i-1] and 'Date' in lines[i-1]:
                        table_start_idx = i - 1
                        separator_idx = i
                        lines.insert(i + 1, new_row)
                        break
            
            if table_start_idx == -1:
                print("Warning: Releases table not found in README.md")
                return False
            
            data_row_indices = []
            for i, line in enumerate(lines):
                if i > separator_idx and re.match(r'^\| \[[\d.]+\]', line):
                    data_row_indices.append(i)
            
            original_count = len(data_row_indices)
            
            while len(data_row_indices) > self.changelog_limit:
                last_idx = data_row_indices.pop()
                if last_idx < len(lines):
                    if lines[last_idx].strip() == '':
                        data_row_indices.append(last_idx)
                        last_idx = data_row_indices.pop()
                    lines[last_idx] = ''
            
            if original_count > self.changelog_limit:
                note = f"*For older versions, see [Releases]({repo_url}/releases)*"
                
                last_data_idx = data_row_indices[-1] if data_row_indices else separator_idx + 1
                
                if last_data_idx + 1 < len(lines):
                    if lines[last_data_idx + 1].strip() == '':
                        lines[last_data_idx + 1] = note
                    elif 'For older versions' not in lines[last_data_idx + 1]:
                        lines.insert(last_data_idx + 1, note)
                else:
                    lines.append(note)
            
            new_content = '\n'.join(lines)
            while '\n\n\n' in new_content:
                new_content = new_content.replace('\n\n\n', '\n\n')
            
            with open(self.readme_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            
            return True
        except Exception as e:
            print(f"Error updating releases table: {e}")
            return False
    
    def generate_changelog_entry(self, version: str, changelog_info: Dict) -> str:
        """Generate formatted changelog entry for a version"""
        change_type = changelog_info.get('change_type', 'fix')
        icon = self.CHANGE_TYPE_ICONS.get(change_type, '📦')
        label = self.CHANGE_TYPE_LABELS.get(change_type, 'Changes')
        
        details = changelog_info.get('details', '')
        changelog = changelog_info.get('changelog', '')
        
        entry_lines = [f"### {version}"]
        entry_lines.append(f"* {icon} **{label}**:")
        
        if details:
            detail_lines = [d.strip() for d in details.split('\n') if d.strip()]
            for detail in detail_lines:
                if detail.startswith('- '):
                    entry_lines.append(f"  {detail}")
                else:
                    entry_lines.append(f"  - {detail}")
        else:
            entry_lines.append(f"  - {changelog}")
        
        entry_lines.append("")
        return '\n'.join(entry_lines)
    
    def update_changelog_section(self, version: str, changelog_info: Dict) -> bool:
        """
        Update Changelog section in README.md
        - Add new version at top
        - Trim to keep only N newest versions
        - Add "See Wiki" link at bottom if trimmed
        """
        try:
            with open(self.readme_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            changelog_section, start_pos, end_pos = self._extract_changelog_section(content)
            
            if not changelog_section:
                print("Warning: Changelog section not found in README.md")
                return False
            
            versions = self._parse_changelog_versions(changelog_section)
            
            new_entry = self.generate_changelog_entry(version, changelog_info)
            
            if versions:
                first_version_pos = versions[0]['position']
                updated_changelog = (
                    changelog_section[:first_version_pos] +
                    new_entry +
                    changelog_section[first_version_pos:]
                )
            else:
                header_end = changelog_section.find('\n\n')
                if header_end != -1:
                    updated_changelog = (
                        changelog_section[:header_end + 2] +
                        new_entry +
                        changelog_section[header_end + 2:]
                    )
                else:
                    updated_changelog = changelog_section + '\n' + new_entry
            
            versions = self._parse_changelog_versions(updated_changelog)
            
            if len(versions) > self.changelog_limit:
                versions_to_keep = versions[:self.changelog_limit]
                last_keep_pos = versions_to_keep[-1]['end_position']
                
                trimmed_changelog = updated_changelog[:last_keep_pos]
                
                see_wiki_note = (
                    f"\n---\n"
                    f"*For older versions, see [Wiki Changelog]({self.WIKI_URL})*\n"
                )
                
                if '---' not in trimmed_changelog[-200:]:
                    trimmed_changelog += see_wiki_note
                
                updated_changelog = trimmed_changelog
            
            new_content = (
                content[:start_pos] +
                updated_changelog +
                content[end_pos:]
            )
            
            with open(self.readme_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            
            return True
        except Exception as e:
            print(f"Error updating changelog section: {e}")
            return False
    
    def _extract_changelog_section(self, content: str) -> Tuple[str, int, int]:
        """Extract Changelog section from README.md content"""
        pattern = r'(## Changelog\n.*?)(?=\n## |\Z)'
        match = re.search(pattern, content, re.DOTALL)
        
        if match:
            return match.group(1), match.start(1), match.end(1)
        
        return None, 0, 0
    
    def _parse_changelog_versions(self, changelog_content: str) -> List[Dict]:
        """Parse version entries from changelog content"""
        versions = []
        pattern = r'### (\d+\.\d+\.\d+(?:-\w+)?)\n'
        
        for match in re.finditer(pattern, changelog_content):
            versions.append({
                'version': match.group(1),
                'position': match.start(),
                'end_position': self._find_version_end(changelog_content, match.end())
            })
        
        return versions
    
    def _find_version_end(self, content: str, start: int) -> int:
        """Find the end position of a version section"""
        next_version = re.search(r'\n### \d+\.\d+\.\d+', content[start:])
        if next_version:
            return start + next_version.start()
        
        hr_pattern = re.search(r'\n---', content[start:])
        if hr_pattern:
            return start + hr_pattern.start()
        
        return len(content)


def update_readme_version_badge(readme_path: Path, version: str, config: Dict = None) -> bool:
    """Convenience function to update version badge"""
    updater = DocumentationUpdater(readme_path, config)
    return updater.update_version_badge(version)


def update_readme_releases_table(readme_path: Path, version: str, changelog_info: Dict, config: Dict = None) -> bool:
    """Convenience function to update releases table"""
    updater = DocumentationUpdater(readme_path, config)
    return updater.update_releases_table(version, changelog_info)


def update_readme_changelog_section(readme_path: Path, version: str, changelog_info: Dict, config: Dict = None) -> bool:
    """Convenience function to update changelog section"""
    updater = DocumentationUpdater(readme_path, config)
    return updater.update_changelog_section(version, changelog_info)


def extract_full_changelog(readme_path: Path) -> str:
    """
    Extract complete Changelog section from README.md
    Returns full markdown content of all versions
    """
    try:
        with open(readme_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        pattern = r'(## Changelog\n.*)'
        match = re.search(pattern, content, re.DOTALL)
        
        if match:
            changelog = match.group(1)
            
            changelog = re.sub(
                r'\n---\n\*For older versions, see \[Wiki Changelog\].*?\*\n*$',
                '',
                changelog
            )
            
            return changelog.strip()
        
        return ""
    except Exception as e:
        print(f"Error extracting changelog: {e}")
        return ""


def format_changelog_for_wiki(changelog_content: str) -> str:
    """
    Format README changelog for Wiki Update-Log.md
    - Convert version headers to include date
    - Add proper formatting
    """
    lines = changelog_content.split('\n')
    formatted_lines = []
    today = datetime.now().strftime('%Y-%m-%d')
    first_version = True
    
    for line in lines:
        version_match = re.match(r'### (\d+\.\d+\.\d+(?:-\w+)?)', line)
        if version_match:
            version = version_match.group(1)
            if first_version:
                formatted_lines.append(f"### v{version} ({today})")
                first_version = False
            else:
                formatted_lines.append(line.replace(f"### {version}", f"### v{version}"))
        elif line.startswith("## Changelog"):
            formatted_lines.append("## Changelog\n")
            formatted_lines.append("> Complete version history synced from README.md\n")
        else:
            formatted_lines.append(line)
    
    return '\n'.join(formatted_lines)


def sync_wiki_changelog(wiki_path: Path, readme_path: Path, version: str, changelog_info: Dict, config: Dict = None) -> bool:
    """
    Update Wiki Update-Log.md with full Changelog
    
    1. Read current wiki content
    2. Update Release History table (limit to 5 entries)
    3. Replace Changelog section with full content from README (no limit)
    """
    try:
        updatelog_path = wiki_path / 'Update-Log.md'
        
        if not updatelog_path.exists():
            print(f"Warning: Update-Log.md not found at {updatelog_path}")
            return False
        
        with open(updatelog_path, 'r', encoding='utf-8') as f:
            wiki_content = f.read()
        
        today = datetime.now().strftime('%Y-%m-%d')
        repo_url = "https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email"
        
        changelog_limit = 5
        if config and 'changelog_limit' in config:
            changelog_limit = config['changelog_limit']
        
        change_type = changelog_info.get('change_type', 'fix')
        icon = DocumentationUpdater.CHANGE_TYPE_ICONS.get(change_type, '📦')
        
        new_row = f"| [v{version}]({repo_url}/releases/tag/v{version}) | {today} | {icon} {changelog_info['changelog']} |"
        
        lines = wiki_content.split('\n')
        table_start_idx = -1
        separator_idx = -1
        
        for i, line in enumerate(lines):
            if '|---------|------|---------|' in line and i > 0:
                if 'Version' in lines[i-1] and 'Date' in lines[i-1]:
                    table_start_idx = i - 1
                    separator_idx = i
                    lines.insert(i + 1, new_row)
                    break
        
        if separator_idx != -1:
            data_row_indices = []
            for i, line in enumerate(lines):
                if i > separator_idx and re.match(r'^\| \[v?[\d.]+\]', line):
                    data_row_indices.append(i)
            
            original_count = len(data_row_indices)
            
            while len(data_row_indices) > changelog_limit:
                last_idx = data_row_indices.pop()
                if last_idx < len(lines):
                    lines[last_idx] = ''
            
            if original_count > changelog_limit:
                note = f"*For older versions, see [Releases]({repo_url}/releases)*"
                
                last_data_idx = data_row_indices[-1] if data_row_indices else separator_idx + 1
                
                found_section = False
                for j in range(last_data_idx + 1, min(last_data_idx + 5, len(lines))):
                    if '---' in lines[j]:
                        found_section = True
                        break
                
                if not found_section and last_data_idx + 1 < len(lines):
                    if lines[last_data_idx + 1].strip() == '':
                        lines[last_data_idx + 1] = note
                    elif 'For older versions' not in lines[last_data_idx + 1]:
                        lines.insert(last_data_idx + 1, note)
        
        wiki_content = '\n'.join(lines)
        while '\n\n\n' in wiki_content:
            wiki_content = wiki_content.replace('\n\n\n', '\n\n')
        
        full_changelog = extract_full_changelog(readme_path)
        
        if full_changelog:
            formatted_changelog = format_changelog_for_wiki(full_changelog)
            
            pattern = r'## Changelog\n.*'
            wiki_content = re.sub(pattern, formatted_changelog, wiki_content, flags=re.DOTALL)
        
        with open(updatelog_path, 'w', encoding='utf-8') as f:
            f.write(wiki_content)
        
        return True
    except Exception as e:
        print(f"Error syncing wiki changelog: {e}")
        return False


def update_wiki_release_history(wiki_path: Path, version: str, changelog_info: Dict) -> bool:
    """
    Update only the Release History table in Wiki Update-Log.md
    """
    try:
        updatelog_path = wiki_path / 'Update-Log.md'
        
        if not updatelog_path.exists():
            print(f"Warning: Update-Log.md not found at {updatelog_path}")
            return False
        
        with open(updatelog_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        today = datetime.now().strftime('%Y-%m-%d')
        repo_url = "https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email"
        
        change_type = changelog_info.get('change_type', 'fix')
        icon = DocumentationUpdater.CHANGE_TYPE_ICONS.get(change_type, '📦')
        
        new_row = f"| [v{version}]({repo_url}/releases/tag/v{version}) | {today} | {icon} {changelog_info['changelog']} |\n"
        
        lines = content.split('\n')
        
        for i, line in enumerate(lines):
            if '|---' in line and 'Version' in lines[i-1] if i > 0 else False:
                lines.insert(i + 1, new_row.strip())
                break
        
        with open(updatelog_path, 'w', encoding='utf-8') as f:
            f.write('\n'.join(lines))
        
        return True
    except Exception as e:
        print(f"Error updating wiki release history: {e}")
        return False


def update_all_readme_docs(readme_path: Path, version: str, changelog_info: Dict, config: Dict = None) -> Dict[str, bool]:
    """
    Update all README.md documentation in one call
    
    Returns dict with results for each operation
    """
    results = {
        'version_badge': False,
        'releases_table': False,
        'changelog_section': False
    }
    
    updater = DocumentationUpdater(readme_path, config)
    
    results['version_badge'] = updater.update_version_badge(version)
    results['releases_table'] = updater.update_releases_table(version, changelog_info)
    results['changelog_section'] = updater.update_changelog_section(version, changelog_info)
    
    return results


if __name__ == '__main__':
    import argparse
    
    parser = argparse.ArgumentParser(description='Update documentation for Cart Quote plugin')
    parser.add_argument('--readme', required=True, help='Path to README.md')
    parser.add_argument('--version', required=True, help='Version number')
    parser.add_argument('--changelog', required=True, help='Changelog message')
    parser.add_argument('--type', default='fix', 
                        choices=['fix', 'feature', 'enhancement', 'performance', 'documentation', 'security'],
                        help='Type of change')
    parser.add_argument('--details', help='Detailed changes (multiline string)')
    parser.add_argument('--limit', type=int, default=5, help='Changelog entry limit')
    
    args = parser.parse_args()
    
    readme_path = Path(args.readme)
    
    changelog_info = {
        'changelog': args.changelog,
        'change_type': args.type,
        'details': args.details or '',
        'full_changelog': f"{DocumentationUpdater.CHANGE_TYPE_ICONS.get(args.type, '📦')} {args.changelog}"
    }
    
    config = {'changelog_limit': args.limit}
    
    results = update_all_readme_docs(readme_path, args.version, changelog_info, config)
    
    print("\n📊 Update Results:")
    for operation, success in results.items():
        status = "✅" if success else "❌"
        print(f"  {status} {operation}")
    
    if all(results.values()):
        print("\n✅ All documentation updated successfully!")
    else:
        print("\n⚠️ Some updates failed. Check output above.")
        sys.exit(1)
