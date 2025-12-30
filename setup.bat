@echo off
echo Setting up Time Tracking Laravel Application...

echo.
echo 1. Installing Composer dependencies...
composer install

echo.
echo 2. Generating application key...
php artisan key:generate

echo.
echo 3. Running database migrations...
php artisan migrate:fresh

echo.
echo 4. Seeding database with sample data...
php artisan db:seed

echo.
echo 5. Creating storage link...
php artisan storage:link

echo.
echo Setup complete! You can now run: php artisan serve
echo.
echo Default login credentials:
echo Admin: username=admin, password=password
echo Employee: username=sarah, password=password
pause