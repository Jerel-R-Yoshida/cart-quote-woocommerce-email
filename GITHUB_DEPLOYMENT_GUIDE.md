# GitHub Repository Setup Instructions

## Setup Complete ✅

Your Git repository is ready for GitHub! Here's what has been configured:

### Current Status:
- ✅ Git repository initialized
- ✅ Created dev branch (for development)
- ✅ Created master branch (for production)
- ✅ Committed cart-quote-woocommerce-email plugin
- ✅ Configured .gitignore for proper file tracking
- ✅ Added security testing documentation
- ✅ Set up GitHub workflows structure

### Repository Structure:
```
. ├── .github/
│ ├── .gitignore
│ ├── README.md
│ └── cart-quote-woocommerce-email/  ← Only this directory is tracked
```

## Branch Strategy

**Development Flow:**
- **dev branch** - For ongoing development and testing
- **master branch** - For production-ready code

**Push Commands:**
- Push to **dev** when developing
- Push to **master** only when you say "push to master" (production deployment)

## Next Steps to Push to GitHub:

### Option 1: Create New Repository on GitHub

1. **Create Repository on GitHub:**
   - Go to https://github.com/new
   - Repository name: `cart-quote-woocommerce-email`
   - Description: "Transform WooCommerce checkout into quote submission system"
   - Public or Private: Choose your preference
   - **Important:** Uncheck "Add a README file"
   - Click "Create repository"

2. **Push to dev branch (for development):**
   ```bash
   # Add GitHub as remote
   git remote add origin https://github.com/YOUR_USERNAME/cart-quote-woocommerce-email.git

   # Push dev branch
   git push -u origin dev

   # Make dev branch as default (optional)
   git branch --set-upstream-to=origin/dev dev
   ```

3. **Push to master branch (for production - ONLY WHEN REQUESTED):**
   ```bash
   # Switch to master branch
   git checkout master

   # Pull latest from dev
   git pull origin dev

   # Push to master
   git push -u origin master

   # Set master as default branch (if needed)
   git push origin master:master
   ```

### Option 2: Push to Existing Repository

1. **Add existing repository:**
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git

   # Push to dev branch first
   git push -u origin dev

   # Later, when ready for production: "push to master"
   git checkout master
   git pull origin dev
   git push -u origin master
   ```

### Option 3: Use SSH (Recommended for future access)

1. **Set up SSH key (if you haven't):**
   ```bash
   # Generate SSH key (if needed)
   ssh-keygen -t ed25519 -C "your_email@example.com"

   # Copy SSH key to GitHub
   cat ~/.ssh/id_ed25519.pub
   ```

2. **Add SSH remote:**
   ```bash
   git remote add origin git@github.com:YOUR_USERNAME/cart-quote-woocommerce-email.git

   # Push to dev branch
   git push -u origin dev

   # Later, when ready for production: "push to master"
   git checkout master
   git pull origin dev
   git push -u origin master
   ```

## 🚀 **Push to Master Commands (Only when requested)**

### Quick Push to Master (Production Deployment):

```bash
# 1. Ensure you're on dev branch and have latest changes
git checkout dev
git pull origin dev

# 2. Create a merge commit or switch to master and merge
git checkout master
git merge dev --no-ff -m "Merge dev to master: Production deployment"

# 3. Push to master
git push origin master

# 4. Update default branch if needed
git push origin master:master
```

### Alternative: Force Push to Master (Clean deployment):

```bash
# 1. Switch to master
git checkout master

# 2. Clean any existing master content
git fetch origin
git reset --hard origin/dev

# 3. Push to master
git push origin master --force-with-lease
```

## Verify Your Repository

After pushing, verify your GitHub repository shows:
- ✅ `cart-quote-woocommerce-email` directory
- ✅ `.gitignore` file
- ✅ `README.md` file
- ✅ `.github/workflows/ci.yml` file
- ✅ **NO** `tests/` directory
- ✅ **NO** `vendor/` directory
- ✅ **NO** `tools/` directory
- ✅ **master** branch as default

## Branch Management Workflow

### Development Workflow:

```bash
# 1. Start development
git checkout dev

# 2. Make changes and commit
git add .
git commit -m "Add new feature"

# 3. Push to dev (not master)
git push origin dev
```

### Production Deployment:

```bash
# Only when you say "push to master":
git checkout dev
git pull origin dev
git checkout master
git merge dev --no-ff -m "Production deployment: New features ready"
git push origin master
```

## Security Tests are Ready

Your security tests are ready to run:
```bash
cd tests
php test-security.php
```

## Project Information

**Repository:** Cart Quote WooCommerce & Email
**Development Branch:** dev
**Production Branch:** master
**Status:** Ready for GitHub deployment
**Security:** ✅ SQL injection and XSS protection implemented

## Troubleshooting

### Issue: Permission denied when pushing
**Solution:**
```bash
# Check remote URL
git remote -v

# If using HTTPS with authentication issues, use SSH instead
```

### Issue: Large files detected
**Solution:** Ensure no large files in `cart-quote-woocommerce-email/`
```bash
# Check for large files
git ls-files -s | awk '{print $4}' | xargs -I {} ls -lh {}
```

### Issue: Cannot push to master
**Solution:**
```bash
# Ensure you have the latest changes from dev
git checkout dev
git pull origin dev

# Try force push if necessary
git push origin master --force-with-lease
```

### Issue: Need to update default branch to master
**Solution:**
```bash
# Rename main branch to master on GitHub
git branch -M master
git push origin master
```

---

## 🎯 **Summary: Push Commands**

### **Push to Dev** (For development):
```bash
git checkout dev
git add .
git commit -m "Your commit message"
git push origin dev
```

### **Push to Master** (Only when requested for production):
```bash
git checkout dev
git pull origin dev
git checkout master
git merge dev --no-ff -m "Production deployment"
git push origin master
```

---

*Ready to deploy to GitHub! 🚀*

## Quick Reference Commands

```bash
# View current branch
git branch

# Switch branches
git checkout dev
git checkout master

# View commits
git log --oneline

# View repository contents
git ls-tree -r --name-only HEAD

# Run security tests
cd tests && php test-security.php
```

---

*Last Updated: 2026-02-14*  
*Branch Strategy: dev for development, master for production*  
*Only push to master when explicitly requested*