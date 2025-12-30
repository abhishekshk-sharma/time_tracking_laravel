@echo off
echo ========================================
echo Time Tracking Laravel Setup - Final Step
echo ========================================
echo.

echo Installing Composer dependencies...
composer install --no-dev --optimize-autoloader

echo.
echo Installing NPM dependencies...
npm install

echo.
echo Building assets...
npm run build

echo.
echo Creating storage link...
php artisan storage:link

echo.
echo Optimizing Laravel...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo ========================================
echo Setup Complete! 
echo ========================================
echo.
echo Your Laravel Time Tracking System is ready!
echo.
echo Access URLs:
echo - Employee: http://localhost/time-tracking-laravel/public/
echo - Admin: http://localhost/time-tracking-laravel/public/admin/dashboard
echo.
echo Next Steps:
echo 1. Update your .env file with database credentials
echo 2. Run: php artisan migrate
echo 3. Import existing data if needed
echo 4. Test the application
echo.
pause