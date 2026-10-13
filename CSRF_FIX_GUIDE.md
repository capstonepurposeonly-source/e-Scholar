# CSRF Token Error - Troubleshooting Guide

## Problem: "Invalid CSRF token" Error

This error occurs when trying to log in to the admin panel. Here are multiple solutions:

---

## ✅ Solution 1: Clear Symfony Cache (Try This First)

```bash
# Clear cache
php bin/console cache:clear

# Clear cache and warmup
php bin/console cache:clear
php bin/console cache:warmup

# If on Windows with permissions issues
rd /s /q var\cache
```

**Then restart your server**:
```bash
# Stop current server (Ctrl+C)
# Then restart
symfony serve
# or
php -S localhost:8000 -t public
```

---

## ✅ Solution 2: Clear Browser Data

### Chrome/Edge:
1. Press `Ctrl + Shift + Delete`
2. Select "Cookies and other site data"
3. Select "Cached images and files"
4. Click "Clear data"
5. Close and reopen browser

### Firefox:
1. Press `Ctrl + Shift + Delete`
2. Select "Cookies" and "Cache"
3. Click "Clear Now"
4. Close and reopen browser

### Alternative - Use Incognito/Private Mode:
- Chrome: `Ctrl + Shift + N`
- Firefox: `Ctrl + Shift + P`
- Edge: `Ctrl + Shift + N`

---

## ✅ Solution 3: Check Session Directory Permissions

### On Windows:

```powershell
# Check if var/sessions directory exists
Test-Path var\sessions

# If it doesn't exist, create it
New-Item -ItemType Directory -Path var\sessions -Force

# Set permissions (run as Administrator if needed)
icacls var\sessions /grant Users:F
```

### Create var/sessions if missing:

```bash
mkdir -p var/sessions
```

---

## ✅ Solution 4: Verify Configuration (Already Updated)

The framework configuration has been updated to:

**File**: `config/packages/framework.yaml`

```yaml
framework:
    secret: '%env(APP_SECRET)%'
    session:
        handler_id: null
        cookie_secure: auto
        cookie_samesite: lax
        storage_factory_id: session.storage.factory.native
    csrf_protection: true
```

This configuration ensures:
- ✅ Sessions are properly configured
- ✅ CSRF protection is enabled
- ✅ Cookie settings are correct

---

## ✅ Solution 5: Verify APP_SECRET

Check your `.env` file has a valid APP_SECRET:

```bash
# View your APP_SECRET
cat .env | Select-String APP_SECRET

# If missing or empty, regenerate it
# Generate a new secret (run this in PowerShell)
$secret = -join ((48..57) + (97..122) + (65..90) | Get-Random -Count 32 | ForEach-Object {[char]$_})
Write-Host "APP_SECRET=$secret"
```

Then update your `.env` file with the generated secret.

---

## ✅ Solution 6: Check Security Configuration

Your `config/packages/security.yaml` should have:

```yaml
firewalls:
    main:
        form_login:
            enable_csrf: true  # ← This should be true
```

This is already correct in your configuration.

---

## ✅ Solution 7: Force Session Start

If the issue persists, update the AdminController login method:

**File**: `src/Controller/AdminController.php`

```php
#[Route('/admin/login', name: 'admin_login')]
public function login(
    AuthenticationUtils $authenticationUtils,
    Request $request
): Response {
    // Force start session
    $request->getSession()->start();
    
    // If user is already logged in, redirect to dashboard
    if ($this->getUser()) {
        return $this->redirectToRoute('admin_dashboard');
    }

    // get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();

    // last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('admin/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
}
```

---

## ✅ Solution 8: Check File Permissions

### Windows:
```powershell
# Give full permissions to var directory
icacls var /grant Users:F /T
```

### Linux/Mac:
```bash
chmod -R 777 var/
```

---

## ✅ Solution 9: Disable CSRF Temporarily (For Testing Only)

**⚠️ NOT RECOMMENDED FOR PRODUCTION**

If you want to test if CSRF is the issue, temporarily disable it:

**File**: `config/packages/security.yaml`

```yaml
firewalls:
    main:
        form_login:
            enable_csrf: false  # Temporarily disable
```

**Important**: Re-enable CSRF after testing!

---

## ✅ Solution 10: Use Different Browser

Sometimes browser extensions interfere with CSRF tokens. Try:
1. Different browser (Chrome → Firefox)
2. Incognito/Private mode
3. Disable browser extensions

---

## 🔍 Debugging Steps

### 1. Check Session Files

```powershell
# List session files
Get-ChildItem var\sessions
```

If no files are created, sessions aren't working.

### 2. Check Symfony Profiler

Access: `http://localhost:8000/_profiler`

Look for:
- Session data
- CSRF token generation
- Form submission details

### 3. Check Logs

```powershell
# View error logs
Get-Content var\log\dev.log -Tail 50
```

Look for session or CSRF-related errors.

### 4. Verify CSRF Token in HTML

View page source on login page and search for:
```html
<input type="hidden" name="_csrf_token" value="...">
```

The value should be a long random string, not empty.

---

## 🚀 Complete Reset Procedure

If nothing works, try this complete reset:

```bash
# 1. Stop server (Ctrl+C)

# 2. Clear everything
rd /s /q var\cache
rd /s /q var\sessions
rd /s /q var\log

# 3. Recreate directories
mkdir var\cache
mkdir var\sessions
mkdir var\log

# 4. Set permissions
icacls var /grant Users:F /T

# 5. Clear Symfony cache
php bin/console cache:clear --no-warmup
php bin/console cache:warmup

# 6. Restart server
symfony serve
```

Then:
1. Clear browser cache/cookies
2. Open browser in incognito mode
3. Navigate to login page
4. Try logging in

---

## ✅ Quick Fix Checklist

Run these commands in order:

```powershell
# 1. Clear cache
php bin/console cache:clear

# 2. Check session directory
if (!(Test-Path var\sessions)) { New-Item -ItemType Directory -Path var\sessions -Force }

# 3. Set permissions
icacls var /grant Users:F /T

# 4. Restart server
# Press Ctrl+C to stop, then:
symfony serve
```

Then:
- Clear browser cache
- Try logging in again

---

## 📞 Still Having Issues?

### Check Your Environment:

```bash
# Check PHP version (should be 8.1+)
php -v

# Check Symfony version
php bin/console --version

# Check if sessions extension is loaded
php -m | Select-String session
```

### Expected Output:
- PHP 8.1 or higher ✅
- Symfony 6.x ✅
- Session extension loaded ✅

---

## 💡 Common Causes

1. **Cache not cleared** - Most common cause
2. **Browser cookies** - Old session cookies
3. **Session directory permissions** - Can't write session files
4. **APP_SECRET changed** - Invalidates existing tokens
5. **Server restart needed** - Changes not applied

---

## ✨ Prevention

To avoid CSRF issues in the future:

1. Always clear cache after config changes
2. Use incognito mode for testing
3. Restart server after security changes
4. Keep sessions directory writable
5. Don't modify APP_SECRET in production

---

## 🎯 Expected Result

After applying fixes, you should:
1. Navigate to: `http://localhost:8000/admin/login`
2. See the login form with no errors
3. Enter credentials
4. Successfully log in
5. Be redirected to dashboard

---

**Created**: October 13, 2026  
**Version**: 1.0  
**Status**: Ready to Use
