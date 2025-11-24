# Git & GitHub Setup Guide

## Current Status
✅ Git repository initialized  
✅ All files staged for commit  
⏳ Need to configure Git user and connect to GitHub

## Step 1: Configure Git User (Required)

Before making commits, configure your Git identity:

```powershell
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

**Use the same email as your GitHub account** for best integration.

## Step 2: Make Initial Commit

```powershell
cd C:\xampp\htdocs\logistix
git commit -m "Initial commit: Logistics Management System setup on XAMPP"
```

## Step 3: Connect to GitHub

### Option A: Connect to Existing Repository

If you already have a GitHub repository:

```powershell
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
git branch -M main
git push -u origin main
```

### Option B: Create New Repository on GitHub

1. Go to https://github.com/new
2. Create a new repository (don't initialize with README)
3. Copy the repository URL
4. Run these commands:

```powershell
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
git branch -M main
git push -u origin main
```

## Step 4: Daily Workflow

### Making Changes and Syncing

1. **Make your code changes**

2. **Check what changed:**
   ```powershell
   git status
   ```

3. **Stage your changes:**
   ```powershell
   git add .
   # Or stage specific files:
   git add path/to/file.php
   ```

4. **Commit your changes:**
   ```powershell
   git commit -m "Description of your changes"
   ```

5. **Push to GitHub:**
   ```powershell
   git push
   ```

### Pulling Changes from GitHub

If you made changes on another machine:

```powershell
git pull
```

## Step 5: Authentication

GitHub no longer accepts passwords. You need to use:

### Option A: Personal Access Token (PAT)

1. Go to GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token with `repo` scope
3. Use token as password when pushing

### Option B: SSH Keys (Recommended)

1. Generate SSH key:
   ```powershell
   ssh-keygen -t ed25519 -C "your.email@example.com"
   ```

2. Add to GitHub:
   - Copy public key: `cat ~/.ssh/id_ed25519.pub`
   - GitHub → Settings → SSH and GPG keys → New SSH key

3. Use SSH URL for remote:
   ```powershell
   git remote set-url origin git@github.com:YOUR_USERNAME/YOUR_REPO_NAME.git
   ```

## Quick Reference Commands

```powershell
# Check status
git status

# See what changed
git diff

# Stage all changes
git add .

# Commit
git commit -m "Your message"

# Push to GitHub
git push

# Pull from GitHub
git pull

# View commit history
git log --oneline

# Check remote connection
git remote -v
```

## Troubleshooting

### "Authentication failed"
- Use Personal Access Token instead of password
- Or set up SSH keys

### "Repository not found"
- Check repository URL is correct
- Ensure repository exists on GitHub
- Verify you have access permissions

### "Updates were rejected"
- Someone else pushed changes
- Pull first: `git pull`, then push again

---

**Ready to set up?** Provide your GitHub username and email, and I'll help configure everything!

