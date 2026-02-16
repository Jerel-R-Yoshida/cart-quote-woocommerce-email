# SSH Authentication - Quick Reference

**This card summarizes the essential SSH setup and commands for daily use.**

---

## 🚀 Daily Workflow

### Start Work Session
```bash
# Start SSH agent (if not auto-started)
eval $(ssh-agent -s)
ssh-add ~/.ssh/id_ed25519

# Verify (should work without passphrase prompt)
ssh -T git@github.com
```

### Regular Git Operations
```bash
cd "D:\Projects\Plugin Builder"
git pull origin dev        # Always works now
git push origin dev        # Always works now
```

### Automated Deployment
```bash
cd "D:\Projects\Plugin Builder\.build"
python deploy.py           # Full deployment
python deploy.py --dev-only  # Test on dev only
python deploy.py --dry-run  # Preview without changes
```

---

## 🔧 Emergency Commands

### Restart SSH Agent
```bash
# Kill existing agent
ssh-agent -k

# Start new agent
eval $(ssh-agent -s)

# Re-add key
ssh-add ~/.ssh/id_ed25519
```

### Check SSH Status
```bash
# List loaded keys
ssh-add -l

# Test GitHub connection
ssh -T git@github.com

# Verify Git remote URL
git remote -v

# Check working tree
git status
```

### Switch Between HTTPS and SSH
```bash
# Switch to SSH (recommended)
git remote set-url origin git@github.com:jerelryoshida-dot/cart-quote-woocommerce-email.git

# Switch to HTTPS (if needed)
git remote set-url origin https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email.git
```

### Test SSH Key
```bash
# Display your public key
cat ~/.ssh/id_ed25519.pub

# Check fingerprint
ssh-keygen -lf ~/.ssh/id_ed25519.pub

# Test with verbose output
ssh -vT git@github.com
```

---

## ✅ Quick Verification

### All-in-One Health Check
```bash
echo "=== SSH Check ==="
ssh-add -l && ssh -T git@github.com

echo "=== Git Remote ==="
cd "D:\Projects\Plugin Builder"
git remote -v && git fetch origin

echo "=== Working Tree ==="
git status --short

echo "=== GitHub CLI ==="
gh auth status

echo "=== Python ==="
python --version
```

### Expected Results
- ✅ SSH key shows in `ssh-add -l`
- ✅ `ssh -T git@github.com` shows "Hi jerelryoshida-dot!"
- ✅ Git remote shows `git@github.com:jerelryoshida-dot/...`
- ✅ `git fetch origin` completes without errors
- ✅ No uncommitted changes in `git status`
- ✅ GitHub CLI shows logged in as `jerelryoshida-dot`
- ✅ Python 3.x.x detected

---

## 📋 Before Running Deployment

### Quick Checklist
```bash
# 1. Check SSH agent
ssh-add -l

# 2. Verify SSH auth
ssh -T git@github.com

# 3. Check remote URL
git remote -v

# 4. Check branch
git branch --show-current

# 5. Check working tree
git status --short

# 6. Check GitHub CLI
gh auth status

# 7. Check Python
python --version
```

**If all show ✅, proceed with deployment:**
```bash
cd "D:\Projects\Plugin Builder\.build"
python deploy.py
```

---

## 🚨 Common Issues

### Issue: SSH prompts for passphrase every time
**Fix:**
```bash
eval $(ssh-agent -s)
ssh-add ~/.ssh/id_ed25519
```

### Issue: Git push fails with authentication error
**Fix:**
```bash
# Verify remote URL
git remote -v

# If HTTPS, switch to SSH:
git remote set-url origin git@github.com:jerelryoshida-dot/cart-quote-woocommerce-email.git

# Then try push again
git push origin dev
```

### Issue: Everything up-to-date but changes not committed
**Fix:**
```bash
git status
git add .
git commit -m "Your message"
git push origin dev
```

### Issue: Pre-push hook blocks push
**Check:**
```bash
git status
git ls-files | wc -l  # Should be 42
```

If >42 files, development files are tracked. Remove them:
```bash
git rm --cached .build/build-config.json
git rm --cached .build/build-zip.py
git commit -m "Remove dev files"
```

---

## 🔐 Key Details

**SSH Key Location:** `~/.ssh/id_ed25519`
**SSH Key Fingerprint:** `SHA256:2AeTJnkzPNYuO66RSKD+93NPuFFe6FUoNrGXhjYLn2o`
**Key Type:** ED25519 (256-bit)
**GitHub Account:** jerelryoshida-dot
**Repository:** cart-quote-woocommerce-email

**SSH Key is configured WITHOUT passphrase** (for automation convenience)

---

## 📁 File Locations

| File | Location |
|------|----------|
| **SSH Key** | `C:\Users\Jerel\.ssh\id_ed25519` |
| **SSH Public Key** | `C:\Users\Jerel\.ssh\id_ed25519.pub` |
| **GitHub** | https://github.com/settings/keys |
| **Deploy Script** | `D:\Projects\Plugin Builder\.build\deploy.py` |
| **Pre-Deployment Checklist** | `D:\Projects\Plugin Builder\.build\PRE_DEPLOYMENT_CHECKLIST.md` |
| **SSH Troubleshooting** | `D:\Projects\Plugin Builder\.build\SSH_TROUBLESHOOTING.md` |

---

## 📞 Support

### Documentation
- **Pre-Deployment Checklist:** `.build/PRE_DEPLOYMENT_CHECKLIST.md`
- **SSH Troubleshooting:** `.build/SSH_TROUBLESHOOTING.md`

### GitHub Documentation
- **Adding SSH keys:** https://docs.github.com/en/authentication/connecting-to-github-with-ssh/adding-a-new-ssh-key-to-your-account
- **Troubleshooting SSH:** https://docs.github.com/en/authentication/connecting-to-github-with-ssh/troubleshooting-ssh-connection-issues

### SSH Documentation
- **OpenSSH manual:** https://man.openbsd.org/ssh
- **SSH agent:** https://man.openbsd.org/ssh-agent

---

## 🔄 Backup Commands

### Backup SSH Keys
```bash
# Copy to safe location
cp ~/.ssh/id_ed25519 ~/ssh-backup/
cp ~/.ssh/id_ed25519.pub ~/ssh-backup/
```

### Backup SSH Config
```bash
cat ~/.ssh/config > ~/ssh-backup/ssh_config_backup.txt
```

### Backup Git Config
```bash
git config --list > ~/git-config-backup.txt
```

---

## 🆘 Emergency

### Restore from Backup
```bash
# Restore SSH keys
cp ~/ssh-backup/id_ed25519 ~/.ssh/
cp ~/ssh-backup/id_ed25519.pub ~/.ssh/

# Set permissions
chmod 600 ~/.ssh/id_ed25519
chmod 644 ~/.ssh/id_ed25519.pub

# Start agent
eval $(ssh-agent -s)
ssh-add ~/.ssh/id_ed25519
```

### Switch Back to HTTPS (if SSH fails)
```bash
git remote set-url origin https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email.git
```

**Then set up Personal Access Token:**
1. Go to https://github.com/settings/tokens
2. Generate new token
3. Use in Git push

---

## 📊 Status Summary

| Component | Status |
|-----------|--------|
| SSH Key Added to GitHub | ✅ |
| SSH Key in Correct Location | ✅ |
| SSH Connection Working | ✅ |
| Git Remote is SSH | ✅ |
| Working Tree Clean | ✅ |
| GitHub CLI Authenticated | ✅ |
| Deploy Script Fixed | ✅ |
| Documentation Complete | ✅ |

**All systems operational!** 🎉

---

## 💡 Pro Tips

1. **Always test SSH before deployment:**
   ```bash
   ssh -T git@github.com
   ```

2. **Keep SSH agent running:**
   ```bash
   eval $(ssh-agent -s)
   ssh-add ~/.ssh/id_ed25519
   ```

3. **Check remote URL before push:**
   ```bash
   git remote -v
   ```

4. **Use dry-run mode to test:**
   ```bash
   python deploy.py --dry-run
   ```

5. **Pre-commit checklist:**
   ```bash
   python .build/PRE_DEPLOYMENT_CHECKLIST.md
   ```

---

**Last Updated:** 2026-02-16
**Maintained by:** Jerel Yoshida
**Project:** Cart Quote WooCommerce Email
