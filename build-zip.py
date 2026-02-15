#!/usr/bin/env python3
"""
ZIP Builder for Cart Quote WooCommerce Plugin

Creates a standards-compliant ZIP archive with forward slashes
for cross-platform compatibility.

Usage: python build-zip.py <version>
Example: python build-zip.py 1.0.9
"""

import os
import zipfile
from pathlib import Path
import sys

def main():
    if len(sys.argv) < 2:
        print("Usage: python build-zip.py <version>")
        print("Example: python build-zip.py 1.0.9")
        sys.exit(1)

    version = sys.argv[1]
    plugin_slug = "cart-quote-woocommerce-email"
    zip_name = f"{plugin_slug}-v{version}.zip"
    root_dir = Path(__file__).parent.resolve()

    print(f"Creating ZIP archive for version {version}...")

    # Patterns to exclude
    exclude_dirs = {'.git', '.github', 'node_modules', '.idea', '.vscode', 'dist', 'test-extract'}
    exclude_files = {'.gitignore', 'composer.json', 'composer.lock', 'package.json',
                    'package-lock.json', 'yarn.lock', 'tsconfig.json',
                    'build-zip.py', 'build-zip.ps1', 'build-zip.bat',
                    'build-zip-v2.ps1', '*.zip', '*.log', '*.swp',
                    '*.swo', '*.tmp', '*.temp', 'Thumbs.db', '.DS_Store'}

    # Create ZIP
    zip_path = root_dir / zip_name

    files_added = 0
    dirs_added = 0
    found_files_list = []

    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(root_dir):
            # Calculate relative path with forward slashes
            try:
                rel_root = Path(root).relative_to(root_dir)
            except ValueError:
                continue

            # Skip excluded directories
            dirs_to_remove = []
            for d in dirs:
                if any(ex in d for ex in exclude_dirs):
                    dirs_to_remove.append(d)
                    continue

            # Remove excluded dirs from dirs list for next iteration
            for d in dirs_to_remove:
                dirs.remove(d)

            for f in files:
                rel_file = rel_root / f
                file_str = str(rel_file).replace('\\', '/')

                # Check if should exclude
                file_name = Path(f).name
                if (file_name in exclude_files or
                    file_str.endswith('.zip') or file_str.endswith('.log') or
                    file_str.endswith('.swp') or file_str.endswith('.swo') or
                    file_str.endswith('.tmp') or file_str.endswith('.temp') or
                    file_str == 'Thumbs.db' or file_str == '.DS_Store'):
                    continue

                # Add file to ZIP
                file_path = Path(root) / f
                zipf.write(file_path, file_str)
                files_added += 1
                found_files_list.append(file_str)

    # Validate ZIP
    print("\nValidating ZIP contents...")

    with zipfile.ZipFile(zip_path, 'r') as zipf:
        has_backslashes = False
        has_root_file = False
        has_core_dir = False

        required_files = [
            'cart-quote-woocommerce-email.php',
            'src/Core/Activator.php',
            'src/Core/Deactivator.php',
            'src/Core/Plugin.php',
            'src/Admin/Admin_Manager.php',
            'src/Database/Quote_Repository.php',
        ]

        found_files = zipf.namelist()

        for name in found_files:
            if '\\' in name:
                has_backslashes = True
                print(f"  ERROR: Backslash found in path: {name}")

            if name == 'cart-quote-woocommerce-email.php':
                has_root_file = True

            if name.startswith('src/Core/'):
                has_core_dir = True

        if has_backslashes:
            print("  ERROR: ZIP contains backslashes in paths")
            sys.exit(1)

        if not has_root_file:
            print("  ERROR: ZIP missing root plugin file")
            sys.exit(1)

        if not has_core_dir:
            print("  ERROR: ZIP missing src/Core directory")
            sys.exit(1)

        missing_files = [req for req in required_files if req not in found_files]
        if missing_files:
            for missing in missing_files:
                print(f"  ERROR: ZIP missing required file: {missing}")
            sys.exit(1)

        print("  No backslashes found")
        print("  Root plugin file present")
        print("  Core directory present")
        print("  All required files present")
        print(f"  Total files in ZIP: {len(found_files)}")

    # Output results
    size_kb = os.path.getsize(zip_path) / 1024
    print(f"\nZIP created successfully: {zip_name}")
    print(f"  Files added: {files_added}")
    print(f"  Size: {size_kb:.2f} KB")

if __name__ == '__main__':
    main()
