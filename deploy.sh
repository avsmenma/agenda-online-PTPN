#!/bin/bash

# 🚀 Script Deployment untuk Laravel Project
# Usage: ./deploy.sh

set -e  # Exit on error

echo "🚀 Starting Deployment Process..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# Get current directory
PROJECT_DIR=$(pwd)
print_info "Project Directory: $PROJECT_DIR"

# Step 1: Backup .env
print_info "Step 1: Backing up .env file..."
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    print_success ".env backed up"
else
    print_error ".env file not found!"
    exit 1
fi

# Step 2: Git Pull
print_info "Step 2: Pulling latest changes from GitHub..."
git pull origin main
if [ $? -eq 0 ]; then
    print_success "Git pull completed"
else
    print_error "Git pull failed!"
    exit 1
fi

# Step 3: Composer Install
print_info "Step 3: Installing/Updating Composer dependencies..."
composer install --no-dev --optimize-autoloader
if [ $? -eq 0 ]; then
    print_success "Composer dependencies installed"
else
    print_error "Composer install failed!"
    exit 1
fi

# Step 4: NPM Install & Build
print_info "Step 4: Installing NPM dependencies..."
npm install
if [ $? -eq 0 ]; then
    print_success "NPM dependencies installed"
else
    print_error "NPM install failed!"
    exit 1
fi

print_info "Building assets..."
npm run build
if [ $? -eq 0 ]; then
    print_success "Assets built successfully"
else
    print_error "Asset build failed!"
    exit 1
fi

# Step 5: Run Migrations
print_info "Step 5: Running database migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    print_success "Migrations completed"
else
    print_error "Migration failed! Check FIX_MIGRATION_ERROR.md for troubleshooting"
    exit 1
fi

# Step 6: Clear Cache
print_info "Step 6: Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_success "Cache cleared"

# Step 7: Optimize
print_info "Step 7: Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Application optimized"

# Step 8: Set Permissions
print_info "Step 8: Setting permissions..."
chmod -R 755 storage bootstrap/cache
if [ -n "$(id -u www-data 2>/dev/null)" ]; then
    chown -R www-data:www-data storage bootstrap/cache
    print_success "Permissions set (www-data)"
else
    print_info "www-data user not found, skipping chown"
fi

# Step 9: Restart PHP-FPM (if exists)
print_info "Step 9: Restarting PHP-FPM..."
if systemctl is-active --quiet php8.2-fpm 2>/dev/null; then
    sudo systemctl restart php8.2-fpm
    print_success "PHP 8.2-FPM restarted"
elif systemctl is-active --quiet php8.1-fpm 2>/dev/null; then
    sudo systemctl restart php8.1-fpm
    print_success "PHP 8.1-FPM restarted"
elif systemctl is-active --quiet php-fpm 2>/dev/null; then
    sudo systemctl restart php-fpm
    print_success "PHP-FPM restarted"
else
    print_info "PHP-FPM not found or not running, skipping restart"
fi

echo ""
print_success "🎉 Deployment completed successfully!"
echo ""
print_info "Next steps:"
echo "  1. Check your website to ensure it's working"
echo "  2. Clear browser cache if you see old content"
echo "  3. Check logs if there are any issues: tail -f storage/logs/laravel.log"
