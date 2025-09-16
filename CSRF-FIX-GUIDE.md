# CSRF Token Issue - Production Deployment Fix

## Problem
When deploying to a live website, CSRF token validation fails because:
1. The frontend forms were not including CSRF tokens
2. CSRF protection is only enabled in production mode
3. Session configuration may conflict with live hosting environments

## Solution Implemented

### 1. Created CSRF Token API Endpoint
- **File**: `api/get-csrf-token.php`
- **Purpose**: Provides CSRF tokens to frontend applications
- **Usage**: `GET /api/get-csrf-token.php` returns `{"success": true, "csrf_token": "..."}`

### 2. Updated Security Middleware
- **File**: `security-middleware.php`
- **Changes**:
  - Improved CSRF validation with better error messages
  - Supports CSRF tokens from JSON input, headers, and POST data
  - Added security event logging for CSRF failures
  - Made `setSecurityHeaders()` method public

### 3. Updated Frontend Login Forms
- **Files Updated**:
  - `public/admin-login.html`
  - `public/login.html` (student login)
  - `public/teacher-login.html`
- **Changes**:
  - Automatically fetch CSRF tokens on page load
  - Include CSRF tokens in all login requests
  - Auto-retry with new token if CSRF validation fails

### 4. Improved Session Configuration
- **File**: `config.php`
- **Changes**:
  - Better handling of secure cookies based on HTTPS availability
  - More specific session name
  - Improved error logging for configuration issues

## Deployment Instructions

### Step 1: Environment Configuration
1. Copy `.env.production` to `.env` on your production server
2. Update the following values in `.env`:
   ```
   DB_HOST=your_production_database_host
   DB_NAME=your_production_database_name  
   DB_USER=your_production_database_user
   DB_PASS=your_secure_production_password
   BASE_URL=https://your-domain.com
   ENVIRONMENT=production
   SECURE_COOKIES=true
   ```

### Step 2: Web Server Configuration
For Apache, ensure your `.htaccess` includes:
```apache
# Enable HTTPS redirect (if using SSL)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### Step 3: PHP Configuration
Ensure your production PHP settings include:
```ini
session.cookie_httponly = 1
session.use_strict_mode = 1
session.cookie_secure = 1  ; Only if using HTTPS
display_errors = 0
log_errors = 1
```

### Step 4: File Permissions
Set appropriate permissions:
```bash
chmod 755 /path/to/your/project
chmod 644 /path/to/your/project/.env
chmod 755 /path/to/your/project/logs
chmod 755 /path/to/your/project/tmp
```

### Step 5: Testing
1. Clear all browser data/cookies
2. Test login functionality on all forms:
   - Admin login (`/public/admin-login.html`)
   - Student login (`/public/login.html`)
   - Teacher login (`/public/teacher-login.html`)
3. Verify CSRF tokens are being generated: `GET /api/get-csrf-token.php`
4. Check security logs: `/logs/security/YYYY-MM-DD.log`

## Troubleshooting

### If CSRF errors persist:
1. **Check environment detection**:
   ```php
   // Add to any PHP file temporarily
   require_once 'env-loader.php';
   var_dump(EnvLoader::isProduction()); // Should return true
   ```

2. **Verify session functionality**:
   ```php
   // Test session start
   session_start();
   $_SESSION['test'] = 'working';
   echo session_id();
   ```

3. **Check HTTPS configuration**:
   - Ensure `$_SERVER['HTTPS']` is set to 'on'
   - Verify SSL certificate is valid
   - Test with `SECURE_COOKIES=false` temporarily

4. **Browser developer tools**:
   - Check Network tab for CSRF token requests
   - Verify tokens are included in request headers/body
   - Check for CORS issues

### Common Issues:
- **"CSRF token missing"**: Frontend not fetching or including tokens
- **"CSRF token invalid"**: Session not persisting or tokens not matching
- **"Session issues"**: Cookie configuration conflicts with hosting environment

## Security Notes
- CSRF protection is only active when `ENVIRONMENT=production`
- All login attempts are logged in production mode
- Session IDs are regenerated every 5 minutes
- Secure cookies require HTTPS to function properly

## Files Modified
- `security-middleware.php` - Enhanced CSRF protection
- `config.php` - Improved session handling
- `api/get-csrf-token.php` - New CSRF token endpoint
- `public/admin-login.html` - Added CSRF support
- `public/login.html` - Added CSRF support  
- `public/teacher-login.html` - Added CSRF support