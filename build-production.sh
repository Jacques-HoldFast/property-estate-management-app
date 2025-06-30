#!/bin/bash

echo "🚀 Building Estatly for Production..."

# Build Frontend
echo "📦 Building Vue.js frontend..."
cd frontend
npm install
npm run build
cd ..

# Prepare Backend
echo "⚙️ Preparing Laravel backend..."
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload --optimize
cd ..

# Create deployment package
echo "📁 Creating deployment package..."
mkdir -p deployment/api
mkdir -p deployment/public_html

# Copy ALL backend files and folders (including hidden files)
echo "📂 Copying Laravel files..."
cp -r backend/* deployment/api/
cp backend/.htaccess deployment/api/

# Clean up debug and test files
echo "🧹 Cleaning up debug files..."
rm -f deployment/api/.htaccess-simple deployment/api/.htaccess-alternative deployment/api/test-api.php

# Copy frontend build (including hidden files like .htaccess)
echo "📂 Copying Vue.js build files..."
cp -r frontend/dist/* deployment/public_html/
cp frontend/dist/.htaccess deployment/public_html/

# Copy production environment
echo "📂 Setting up production environment..."
cp backend/.env.production deployment/api/.env

# Create zip files for easy cPanel upload
echo "📦 Creating zip files for deployment..."
cd deployment

# Create API zip file (extract contents directly, not in folder)
echo "📦 Zipping Laravel API files..."
cd api
zip -r ../estatly-api.zip . -q
cd ..
mv estatly-api.zip api/

# Create frontend zip file (extract contents directly, not in folder)
echo "📦 Zipping Vue.js frontend files..."
cd public_html
zip -r ../estatly-frontend.zip . -q
cd ..
mv estatly-frontend.zip public_html/

cd ..

echo "✅ Production build completed!"
echo "📂 Files ready in ./deployment/ folder"
echo ""
echo "🚀 Easy cPanel Deployment:"
echo "1. Upload deployment/api/estatly-api.zip to your cPanel /public_html/estatly/api/ folder"
echo "2. Extract estatly-api.zip in cPanel File Manager (files extract directly to /api/ root)"
echo "3. Upload deployment/public_html/estatly-frontend.zip to your cPanel /public_html/estatly/ folder"
echo "4. Extract estatly-frontend.zip in cPanel File Manager (files extract directly to /estatly/ root)"
echo "5. Delete both zip files after extraction"
echo "6. Set folder permissions: storage/ and bootstrap/cache/ to 755"
echo "7. Your app will be live at: https://buildbyholdfast.site/estatly/"