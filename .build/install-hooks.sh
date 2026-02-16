#!/bin/bash
#
# Install Git hooks for plugin repository validation + documentation updates
#

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

echo ""
echo "========================================"
echo "  Git Hooks Installer"
echo "========================================"
echo ""

if [ ! -d "$REPO_ROOT/.git" ]; then
    echo "❌ Error: Not in a Git repository"
    echo "Run this script from the Plugin Builder root directory"
    exit 1
fi

mkdir -p "$REPO_ROOT/.git/hooks"

echo "📦 Installing hooks..."
echo ""

# Pre-push hook (validation + documentation)
if [ -f "$SCRIPT_DIR/hooks/pre-push" ]; then
    cp "$SCRIPT_DIR/hooks/pre-push" "$REPO_ROOT/.git/hooks/pre-push"
    chmod +x "$REPO_ROOT/.git/hooks/pre-push"
    echo "   ✅ pre-push (validation + documentation)"
else
    echo "   ❌ pre-push not found"
fi

# Pre-push Python script
if [ -f "$SCRIPT_DIR/hooks/pre-push.py" ]; then
    echo "   ✅ pre-push.py (main logic)"
else
    echo "   ❌ pre-push.py not found"
fi

# Post-push hook (wiki updates after push)
if [ -f "$SCRIPT_DIR/hooks/post-push" ]; then
    cp "$SCRIPT_DIR/hooks/post-push" "$REPO_ROOT/.git/hooks/post-receive"
    chmod +x "$REPO_ROOT/.git/hooks/post-receive"
    echo "   ✅ post-receive (wiki updates)"
else
    echo "   ⚠️  post-push not found"
fi

# Wiki processor
if [ -f "$SCRIPT_DIR/hooks/process-wiki-update.py" ]; then
    echo "   ✅ process-wiki-update.py (wiki sync)"
else
    echo "   ⚠️  process-wiki-update.py not found"
fi

# Configuration
if [ -f "$SCRIPT_DIR/hook-config.json" ]; then
    echo "   ✅ hook-config.json (configuration)"
else
    echo "   ⚠️  hook-config.json not found"
fi

echo ""
echo "========================================"
echo "  ✅ Git hooks installed!"
echo "========================================"
echo ""
echo "📋 Active Features:"
echo ""
echo "   Pre-Push (before git push):"
echo "   ├─ Validate only plugin files are tracked"
echo "   ├─ DEV branch: Prompt for changelog, update README.md"
echo "   └─ MASTER branch: Parse commits, schedule wiki update"
echo ""
echo "   Post-Receive (after git push):"
echo "   └─ Process pending wiki updates"
echo ""
echo "📝 Usage:"
echo ""
echo "   git push origin dev"
echo "   → Prompts for changelog"
echo "   → Updates README.md (version badge, releases, changelog)"
echo "   → Commits README.md changes"
echo ""
echo "   git push origin master"
echo "   → Parses commit messages"
echo "   → Schedules wiki update"
echo "   → Wiki synced automatically after push"
echo ""
echo "⏭️  Skip hooks:"
echo "   git push --no-verify origin dev"
echo "   git commit -m \"message [skip docs]\""
echo ""
echo "📚 Manual wiki update:"
echo "   python .build/hooks/process-wiki-update.py"
echo ""
