# SSH Authentication Troubleshooting Guide

This guide helps you troubleshoot SSH authentication issues for GitHub.

---

## Quick Diagnostics

### Check SSH Key Status
```bash
# List loaded SSH keys
ssh-add -l

# Test SSH connection to GitHub
ssh -T git@github.com
```

**Expected output:**
```
Hi jerelryoshida-dot! You've successfully authenticated...
```

**If you see "Could not open a connection to your authentication agent":**
- SSH agent is not running
- Run: `eval $(ssh-agent -s)` then `ssh-add ~/.ssh/id_ed25519`

**If you see "Permission denied (publickey)":**
- SSH key not recognized by GitHub
- See section "SSH Key Not Recognized by GitHub" below

---

## Common Issues and Solutions

---

### Issue 1: "Could not open a connection to your authentication agent"

**Symptoms:**
```
Could not open a connection to your authentication agent
```

**Diagnosis:**
SSH agent is not running or not accessible.

**Solutions:**

1. **Start SSH agent:**
   ```bash
   eval $(ssh-agent -s)
   ```

2. **Add key to agent:**
   ```bash
   ssh-add ~/.ssh/id_ed25519
   ```

3. **Verify it works:**
   ```bash
   ssh-add -l  # Should show your key
   ssh -T git@github.com  # Should work without passphrase prompt
   ```

---

### Issue 2: "Permission denied (publickey)"

**Symptoms:**
```
git@github.com: Permission denied (publickey)
```

**Diagnosis:**
SSH key exists locally but GitHub doesn't recognize it.

**Solutions:**

#### Option A: Verify Key is Added to GitHub
1. Go to: https://github.com/settings/keys
2. Scroll to "SSH Keys" section
3. Do you see your key listed?
4. Does it have a green checkmark icon?

**If NOT found:**
- Click "New SSH key"
- Paste your key (starts with `ssh-ed25519`)
- Title: `Windows Dev Machine - Cart Quote Plugin`
- Click "Add SSH key"
- Save

#### Option B: Check Key Format
```bash
# Display your key
cat ~/.ssh/id_ed25519.pub

# Should start with:
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAA...
```

**If key is empty or wrong:**
```bash
# Generate new key
ssh-keygen -t ed25519 -C "your-email@example.com" -f ~/.ssh/id_ed25519_new
```

#### Option C: Check Key Permissions
```bash
# Check file permissions (Unix-like systems)
ls -la ~/.ssh/

# Should show:
# -rw------- 1 user user   444 Jan 1 00:00 id_ed25519
# -rw-r--r-- 1 user user    91 Jan 1 00:00 id_ed25519.pub

# If permissions are wrong, fix them:
chmod 600 ~/.ssh/id_ed25519
chmod 644 ~/.ssh/id_ed25519.pub
```

---

### Issue 3: Passphrase Prompt Every Time

**Symptoms:**
You're asked for your SSH key passphrase for every `git push`, `git pull`, etc.

**Diagnosis:**
SSH agent is not configured to remember your key.

**Solutions:**

#### Solution 1: Use SSH Agent (Recommended)
```bash
# Start SSH agent
eval $(ssh-agent -s)

# Add key (enter passphrase once)
ssh-add ~/.ssh/id_ed25519

# Verify key is loaded
ssh-add -l

# Now SSH commands work without prompts
ssh -T git@github.com
```

#### Solution 2: Configure Auto-Start (Permanent)
1. Open terminal profile settings
2. Add to startup commands:
   ```bash
   if [ -z "$SSH_AUTH_SOCK" ]; then
      eval $(ssh-agent -s) > /dev/null
      ssh-add ~/.ssh/id_ed25519 2>/dev/null
   fi
   ```
3. Save and restart terminal

#### Solution 3: Generate Key Without Passphrase
```bash
# Generate new key (no passphrase)
ssh-keygen -t ed25519 -C "your-email@example.com" -f ~/.ssh/id_ed25519 -N ""

# Add to GitHub
cat ~/.ssh/id_ed25519.pub | clip
# Paste in https://github.com/settings/keys

# Test
ssh -T git@github.com
```

**Note:** Keys without passphrases are convenient for automation but less secure. Use a passphrase if you're concerned about security.

---

### Issue 4: Git Remote Uses HTTPS Instead of SSH

**Symptoms:**
- Push fails with authentication errors
- Error messages show `https://github.com`
- No SSH prompts, but asks for username/password

**Diagnosis:**
```bash
git remote -v
```

**If shows:**
```
origin  https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email.git (fetch)
origin  https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email.git (push)
```

**Solution:**
```bash
# Switch to SSH
git remote set-url origin git@github.com:jerelryoshida-dot/cart-quote-woocommerce-email.git

# Verify
git remote -v
```

**Expected output:**
```
origin  git@github.com:jerelryoshida-dot/cart-quote-woocommerce-email.git (fetch)
origin  git@github.com:jerelryoshida-dot/cart-quote-woocommerce-email.git (push)
```

---

### Issue 5: "Could not resolve hostname github.com"

**Symptoms:**
```
ssh: connect to host github.com port 22: Connection timed out
```

**Diagnosis:**
Network or DNS issue.

**Solutions:**

1. **Check internet connection:**
   ```bash
   ping github.com
   ```

2. **Try different network:**
   - Switch from VPN if using one
   - Try mobile hotspot

3. **Check firewall:**
   - Port 22 must be open
   - SSH client must be allowed through firewall

4. **Try HTTPS (workaround):**
   ```bash
   git remote set-url origin https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email.git
   git push origin dev
   ```
   *(Then set up Personal Access Token, but this is less secure for automation)*

---

### Issue 6: Deploy Script Fails at Git Push

**Symptoms:**
- Script runs until push step
- Error during `git push origin dev`
- Error says "Permission denied" or similar

**Diagnosis:**
```bash
# Manually try the same command
cd "D:\Projects\Plugin Builder"
git push origin dev -v  # Verbose output
```

**Common Causes:**

1. **SSH Key Not Added to GitHub**
   - Verify: https://github.com/settings/keys
   - Check key fingerprint matches

2. **SSH Agent Not Running**
   ```bash
   eval $(ssh-agent -s)
   ssh-add ~/.ssh/id_ed25519
   git push origin dev
   ```

3. **Branch Protection Rules**
   - Check GitHub → Settings → Branches
   - Ensure `dev` branch allows direct pushes
   - May need to create pull request instead

4. **Git Credentials Cached**
   - Delete old credentials from Windows Credential Manager
   - Run: `cmdkey /delete:git:https://github.com`

5. **Pre-Push Hook Block**
   - Check if development files are tracked in Git
   - Should be 42 plugin files only

---

### Issue 7: SSH Key Not Found by Git

**Symptoms:**
```
git@github.com: Permission denied (publickey)
```
But you know the key is in `~/.ssh/id_ed25519`

**Diagnosis:**
```bash
# Check if Git is finding your key
ls -la ~/.ssh/

# Look for id_ed25519 and id_ed25519.pub
```

**Solutions:**

1. **Rename key to standard filename:**
   ```bash
   mv ~/.ssh/id_ed25519_new ~/.ssh/id_ed25519
   mv ~/.ssh/id_ed25519_new.pub ~/.ssh/id_ed25519.pub
   ```

2. **Check SSH config:**
   ```bash
   cat ~/.ssh/config

   # Should not have problematic entries
   # If exists, remove or fix
   ```

3. **Test SSH config:**
   ```bash
   ssh -vT git@github.com
   ```

   Look for lines like:
   ```
   debug1: Offering public key: /c/Users/user/.ssh/id_ed25519
   ```

---

## Advanced Troubleshooting

---

### View Detailed SSH Debug Output

```bash
ssh -vT git@github.com
```

**Key lines to check:**
```
debug1: Offering public key: /path/to/id_ed25519
debug1: Server accepts key: pkalg ssh-rsa blablabla
debug1: Authentication succeeded (publickey)
Hi username! You've successfully authenticated...
```

**If you see "Could not load private key":**
- Key file permissions wrong
- File doesn't exist
- Wrong file path

---

### List All SSH Keys

```bash
# List all keys in ~/.ssh
ls -la ~/.ssh/

# Should see:
# id_ed25519
# id_ed25519.pub
# id_ed25519_old (backup)
# id_ed25519_old.pub (backup)
# known_hosts
```

---

### Test Specific Key

```bash
# Use specific key file
ssh -i ~/.ssh/id_ed25519 -T git@github.com
```

---

### Reset SSH Agent

```bash
# Kill existing agent
ssh-agent -k

# Start new agent
eval $(ssh-agent -s)

# Re-add key
ssh-add ~/.ssh/id_ed25519
```

---

## Prevention: Best Practices

1. **Always add SSH key to GitHub first**
   - Verify in GitHub UI before testing locally

2. **Keep SSH agent running**
   - Set up auto-start for convenience
   - Add key once per session

3. **Use SSH URL for Git remotes**
   - `git@github.com:username/repo.git`
   - Not `https://github.com/username/repo.git`

4. **Use Personal Access Token only when necessary**
   - Tokens expire and require updates
   - SSH keys don't expire (no expiration for automation)

5. **Regularly rotate SSH keys (optional)**
   - For high-security environments
   - Keep backups of old keys

---

## Getting Help

If you've tried all solutions above and still have issues:

1. **Check GitHub Status:**
   - https://www.githubstatus.com
   - Is GitHub experiencing issues?

2. **Check SSH Documentation:**
   - https://docs.github.com/en/authentication/connecting-to-github-with-ssh

3. **Check OpenSSH Documentation:**
   - https://man.openbsd.org/ssh

4. **Check deploy.py logs:**
   - Look for error messages
   - Check working tree status

---

## Emergency: Use HTTPS (Last Resort)

If SSH authentication is completely broken:

1. **Generate Personal Access Token:**
   - https://github.com/settings/tokens
   - Select scopes: `repo` (all), `workflow`
   - Copy token (won't be shown again)

2. **Set up credential helper:**
   ```bash
   git config --global credential.helper manager
   ```

3. **Push using HTTPS:**
   ```bash
   git push origin dev
   # Username: jerelryoshida-dot
   # Password: [paste token]
   ```

**Note:** This will store the token in Windows Credential Manager. Tokens will expire (30/60/90 days), requiring regeneration.

---

## Summary Table

| Issue | Symptoms | Quick Fix |
|-------|----------|-----------|
| Agent not running | "Could not open agent" | `eval $(ssh-agent -s)` |
| Key not recognized | "Permission denied" | Add key to GitHub |
| Passphrase prompt | Asks for password | Use SSH agent |
| HTTPS remote | Authentication errors | `git remote set-url origin git@github.com:...` |
| Hostname resolution | "Connection timeout" | Check internet/VPN |
| Git can't find key | "Could not load private key" | Rename to `id_ed25519` |

---

For more information, see:
- **Quick Reference:** `.build/SSH_QUICK_REFERENCE.md`
- **Pre-Deployment Checklist:** `.build/PRE_DEPLOYMENT_CHECKLIST.md`
