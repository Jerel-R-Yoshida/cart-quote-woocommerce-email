# 🎯 Ollama Installation Guide for Windows

## Current Status
Ollama automatic installation encountered issues. Please follow these manual steps:

## Manual Installation Steps

### Method 1: Direct Download (Recommended)

1. **Download Ollama Installer:**
   - Go to: https://ollama.com/download
   - Click "Download Ollama for Windows"
   - Save `OllamaSetup.exe` to your Desktop

2. **Run the Installer:**
   - Double-click `OllamaSetup.exe`
   - Click "Install" button
   - Wait for installation to complete (~3 minutes)

3. **Start Ollama Service:**
   - Open Command Prompt (Admin)
   - Run: `sc start Ollama`

4. **Verify Installation:**
   - Run: `ollama --version`
   - Expected output: `Ollama 0.16.x`

### Method 2: PowerShell Script (Alternative)

```powershell
# Run PowerShell as Administrator
irm https://ollama.com/install.ps1 | iex
```

### Method 3: Winget (Package Manager)

```powershell
# Run PowerShell as Administrator
winget install Ollama.Ollama
```

## After Installation

1. **Check if Ollama is running:**
   ```bash
   ollama --version
   ```

2. **Start the Ollama service (if not auto-started):**
   ```bash
   sc start Ollama
   ```

3. **Download the llama3.2 (3B) model:**
   ```bash
   ollama pull llama3.2:3b
   ```

4. **Test the model:**
   ```bash
   ollama run llama3.2:3b
   ```

## Troubleshooting

### Issue: "ollama: command not found"

**Solution:**
1. Restart your terminal/command prompt
2. Ensure Ollama service is running: `sc start Ollama`
3. Restart your computer

### Issue: "Cannot start Ollama service"

**Solution:**
1. Run Command Prompt as Administrator
2. Check service status: `sc query Ollama`
3. Try reinstalling Ollama

### Issue: GPU acceleration not working

**Solution:**
1. Verify NVIDIA drivers: `nvidia-smi`
2. Restart Ollama service: `sc stop Ollama && sc start Ollama`
3. Check Ollama logs: `Get-Content $env:USERPROFILE\.ollama\ollama.log`

## Next Steps

Once Ollama is installed:

1. **Download llama3.2:3b model:**
   ```bash
   ollama pull llama3.2:3b
   ```

2. **Test coding capabilities:**
   ```bash
   ollama run llama3.2:3b
   ```

3. **Verify GPU acceleration:**
   - Monitor GPU usage during inference
   - Open Task Manager → Performance → GPU
   - Should see increased GPU utilization

## System Requirements (Already Met)

✅ NVIDIA GeForce RTX 3050 (6GB VRAM)
✅ CUDA 13.1
✅ Driver Version 591.86
✅ Sufficient RAM (verified via nvidia-smi)

## File Locations

- **Ollama Installation:** `C:\Program Files\Ollama\`
- **Ollama Service:** Windows Service "Ollama"
- **Ollama Logs:** `$env:USERPROFILE\.ollama\ollama.log`
- **Model Cache:** `$env:USERPROFILE\.ollama\models\`

## Quick Reference

```bash
# Check version
ollama --version

# List installed models
ollama list

# Start service
sc start Ollama

# Stop service
sc stop Ollama

# View logs
Get-Content $env:USERPROFILE\.ollama\ollama.log

# Download model
ollama pull llama3.2:3b

# Run chat
ollama run llama3.2:3b

# Check GPU
nvidia-smi
```

---

**Status:** Installation pending manual execution
**Date:** 2026-02-16
**User:** Jerel
