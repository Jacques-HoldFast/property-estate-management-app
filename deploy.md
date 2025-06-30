# Estatly - Deployment Guide for cPanel Shared Hosting

## Prerequisites
1. cPanel hosting account with PHP 8.1+ support
2. MariaDB/MySQL database created in cPanel
3. SSH access (if available) or File Manager access
4. Domain pointed to your hosting account

## 1. Backend (Laravel) Deployment

### Step 1: Prepare Files for Upload
```bash
# In your local backend directory
cd backend

# Install production dependencies
composer install --optimize-autoloader --no-dev

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Create optimized autoloader
composer dump-autoload --optimize
```

### Step 2: Upload Files via cPanel File Manager
1. **Create folder structure in cPanel for https://buildbyholdfast.site/estatly/:**
   - Create `/public_html/estatly/` for your application
   - Create `/public_html/estatly/api/` for Laravel backend

2. **Upload Laravel files:**
   - Upload ALL Laravel files and folders to `/public_html/estatly/api/`
   - This includes: `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`, `.env`, `artisan`, `composer.json`, etc.

3. **Upload Vue.js files:**
   - Upload all built Vue.js files (from `dist/` folder) to `/public_html/estatly/`

4. **Final file structure should look like:**
```
public_html/
└── estatly/            # Your application folder
    ├── api/            # Laravel application root
    │   ├── app/
    │   ├── bootstrap/
    │   ├── config/
    │   ├── database/
    │   ├── resources/
    │   ├── routes/
    │   ├── storage/
    │   ├── vendor/
    │   ├── .env        # Rename .env.production to .env
    │   ├── artisan
    │   ├── composer.json
    │   └── public/     # Laravel public folder
    │       ├── index.php
    │       └── ...
    ├── index.html      # Vue.js frontend entry point
    ├── assets/         # Vue.js built assets
    └── .htaccess       # Frontend routing rules
```

### Step 3: Configure Database in cPanel
1. Go to **MySQL Databases** in cPanel
2. Create a new database: `yourusername_estatly`
3. Create a new user with a strong password
4. Add user to database with **ALL PRIVILEGES**
5. Note down the database name, username, and password

### Step 4: Update .env File
Update `/public_html/estatly/api/.env` with your database credentials:
```env
DB_HOST=localhost
DB_DATABASE=buildbyh_estatly
DB_USERNAME=buildbyh_estatly
DB_PASSWORD=estatly123!
```

### Step 5: Set Permissions
Set the following folder permissions via cPanel File Manager:
- `/storage/` and all subdirectories: **755**
- `/bootstrap/cache/`: **755**

### Step 6: Run Migrations
If you have SSH access:
```bash
cd public_html/api
php artisan migrate --force
php artisan db:seed --force  # if you have seeders
```

If no SSH access, create a temporary migration script:
```php
<?php
// Create this file as migrate.php in public_html/estatly/api/
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('migrate', ['--force' => true]);
echo "Migrations completed!";
```

## 2. Frontend (Vue.js) Deployment

### Step 1: Configure API Base URL for Production
First, make sure your frontend is configured for production:

Create or update `frontend/.env.production`:
```env
VITE_API_BASE_URL=https://buildbyholdfast.site/estatly/api
VITE_APP_NAME=Estatly
```

### Step 2: Build Vue.js for Production
```bash
# Navigate to the frontend directory
cd frontend

# Install dependencies (if not already installed)
npm install

# Build for production (this creates the /dist folder)
npm run build
```

**What happens during build:**
- Vite compiles and optimizes your Vue.js code
- Creates a `/dist` folder with production-ready files
- Minifies CSS, JavaScript, and optimizes assets
- Generates `index.html`, `assets/` folder, and other static files

### Step 3: Upload Built Files
1. After running `npm run build`, you'll have a `/dist` folder in your frontend directory
2. Upload ALL contents of the `/dist` folder to `/public_html/estatly/`
3. This includes:
   - `index.html` (main entry point)
   - `assets/` folder (CSS, JS, images)
   - `.htaccess` file (with routing rules and security headers)
   - Any other generated files

**Important:** Upload the **contents** of the `dist/` folder, not the `dist/` folder itself. The generated `.htaccess` file is already optimized for your setup.

### Step 4: Verify Frontend Build
Before uploading, you can test your build locally:
```bash
# After building, preview the production build
npm run preview
```
This starts a local server to test the built files.

**Troubleshooting Frontend Build:**
- **Build fails:** Check for TypeScript errors or missing dependencies
- **Blank page after upload:** Check browser console for API URL errors
- **Assets not loading (404 errors):** Verify all files from `/dist` were uploaded correctly to `/public_html/estatly/`
- **API calls fail:** Confirm `VITE_API_BASE_URL` matches your server setup
- **Assets loading from wrong path:** The build process automatically sets the correct base URL (`/estatly/`) for subdirectory deployment

## 3. Configure URL Rewriting

### For Laravel API:

**Option 1: Create .htaccess in /public_html/estatly/api/ (Recommended)**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # Redirect all requests to the public folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Option 2: Create .htaccess in /public_html/estatly/api/public/**
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Recommended:** Use Option 1 - it's simpler and handles the Laravel routing properly.

### For Vue.js SPA (.htaccess is automatically generated in dist/ folder):
The Vue.js build process automatically generates an optimized `.htaccess` file in the `dist/` folder with the following content:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle API requests - redirect to Laravel backend
    RewriteRule ^api/(.*)$ api/public/$1 [L]
    
    # Handle Vue.js SPA routing for subdirectory
    RewriteBase /estatly/
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /estatly/index.html [L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

**Important:** Use the `.htaccess` file from your `dist/` folder instead of manually creating one. It includes security headers for better protection.

## 4. SSL Certificate
1. Go to **SSL/TLS** in cPanel
2. Enable **Let's Encrypt** free SSL
3. Force HTTPS redirects

## 5. Testing
1. Visit `https://buildbyholdfast.site/estatly/` - should show Vue.js app
2. Test API: `https://buildbyholdfast.site/estatly/api/` - should show Laravel welcome or 404
3. Test a specific endpoint: `https://buildbyholdfast.site/estatly/api/dashboard/stats`

## 6. Troubleshooting

### Common Issues:
1. **500 Internal Server Error**: Check file permissions and .env file
2. **Database connection errors**: Verify database credentials
3. **CORS issues**: Update `config/cors.php` in Laravel
4. **File not found**: Check .htaccess files and URL rewriting
5. **Route not found (e.g., sanctum/csrf-cookie)**: 
   - **First, test if Laravel is working**: Visit `https://buildbyholdfast.site/estatly/api/test-api.php`
   - **Test Laravel public folder**: Try `https://buildbyholdfast.site/estatly/api/public/`
   - **Test basic Laravel**: Try `https://buildbyholdfast.site/estatly/api/public/index.php`
   - **✅ SOLUTION**: This deployment is configured to use `/api/public/` directly
     * Frontend API calls: `https://buildbyholdfast.site/estatly/api/public/sanctum/csrf-cookie`
     * All API endpoints: `https://buildbyholdfast.site/estatly/api/public/api/...`
   - **Check Laravel logs**: Look in `/storage/logs/laravel.log`

### Enable Error Reporting (temporarily):
In `/public_html/estatly/api/.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Remember to set `APP_DEBUG=false` after fixing issues!

## 7. Maintenance Commands

If you have SSH access, you can run these periodically:
```bash
# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update dependencies
composer install --optimize-autoloader --no-dev
```