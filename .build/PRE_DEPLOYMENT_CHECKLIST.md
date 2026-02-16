# Pre-Deployment Checklist

Before running `python deploy.py`, verify all the following:

---

## Environment Setup

- [ ] **SSH Key Available**
  ```bash
  ssh-add -l  # Should show: 256 SHA256:... jerel@dev (ED25519)
  ```
  - ✅ SSH key exists in `~/.ssh/id_ed25519`
  - ✅ Key is added to GitHub (https://github.com/settings/keys)
  - ✅ SSH connection works: `ssh -T git@github.com` (no passphrase prompt)

- [ ] **SSH Agent Running**
  ```bash
  eval $(ssh-agent -s)
  ssh-add ~/.ssh/id_ed25519
  ```
  - ✅ SSH agent is running (shows Agent pid xxxx)
  - ✅ Key is loaded in agent (shows `Identity added`)

---

## Git Configuration

- [ ] **Remote URL is SSH (not HTTPS)**
  ```bash
  git remote -v
  ```
  - ✅ Shows `git@github.com:jerelryoshida-dot/...` (not `https://github.com/...`)
  - ✅ Both fetch and push URLs are SSH

- [ ] **Current Branch**
  ```bash
  git branch --show-current
  ```
  - ✅ Branch is `dev` (default deployment branch)

- [ ] **Working Tree Clean**
  ```bash
  git status
  ```
  - ✅ Shows "nothing to commit, working tree clean"
  - ⚠️ If not clean, commit changes first

---

## GitHub CLI

- [ ] **Authenticated**
  ```bash
  gh auth status
  ```
  - ✅ Shows "Logged in to github.com as jerelryoshida-dot"

- [ ] **Correct Account**
  - ✅ Account is `jerelryoshida-dot`

---

## Script Dependencies

- [ ] **Python 3 Installed**
  ```bash
  python --version
  ```
  - ✅ Python 3.x.x

- [ ] **Deploy Script Exists**
  ```bash
  ls .build/deploy.py
  ```
  - ✅ File exists

- [ ] **Config Exists**
  ```bash
  ls .build/deploy-config.json
  ```
  - ✅ File exists

---

## Manual Verification

- [ ] **All Code Changes Committed**
  - ✅ All local changes are committed
  - ✅ No uncommitted files

- [ ] **Version Numbers Consistent**
  ```bash
  # Check version in main plugin file
  grep "Version:" cart-quote-woocommerce-email.php | head -1

  # Check version in Plugin.php
  grep "private \$version" src/Core/Plugin.php

  # Should all match
  ```
  - ✅ Version matches across all files

- [ ] **No Sensitive Data in Commits**
  ```bash
  git status
  ```
  - ✅ No .env files, keys, tokens in working directory
  - ✅ No sensitive data in commit messages

---

## Optional: Test SSH Push

Before full deployment, test SSH authentication:

```bash
# Create test commit
echo "# Test SSH" > test.tmp
git add test.tmp
git commit -m "Test SSH auth"
git push origin dev

# If successful, clean up
git rm test.tmp
git commit -m "Clean up test"
git push origin dev
```

---

## Final Confirmation

If all checkboxes pass → **Proceed with deployment**:
```bash
cd "D:\Projects\Plugin Builder\.build"
python deploy.py
```

---

## Quick Health Check (All-in-One)

Run this to verify everything:

```bash
# Test SSH agent and auth
echo "=== SSH Check ==="
ssh-add -l && ssh -T git@github.com

# Test Git remote
echo "=== Git Remote ==="
cd "D:\Projects\Plugin Builder"
git remote -v && git fetch origin

# Check working tree
echo "=== Working Tree ==="
git status --short

# Check GitHub CLI
echo "=== GitHub CLI ==="
gh auth status

# Check Python
echo "=== Python ==="
python --version
```

All checks should pass before deploying.

---

## Troubleshooting

### Issue: "Could not open a connection to your authentication agent"
**Solution:**
```bash
eval $(ssh-agent -s)
ssh-add ~/.ssh/id_ed25519
```

### Issue: SSH prompts for passphrase every time
**Solution:**
```bash
eval $(ssh-agent -s)
ssh-add ~/.ssh/id_ed25519
```
Or set up SSH agent auto-start (see SSH documentation)

### Issue: Git push fails with "Permission denied"
**Solution:**
1. Check remote URL: `git remote -v` (should be SSH)
2. Check SSH key added to GitHub: https://github.com/settings/keys
3. Test SSH connection: `ssh -T git@github.com`

### Issue: "Everything up-to-date" but changes not committed
**Solution:**
```bash
git status
git add .
git commit -m "Your message"
git push origin dev
```

---

## Support

- **SSH Troubleshooting:** `.build/SSH_TROUBLESHOOTING.md`
- **Quick Reference:** `.build/SSH_QUICK_REFERENCE.md`
- **Deploy Script:** `.build/deploy.py`
