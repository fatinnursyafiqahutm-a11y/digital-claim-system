#!/bin/sh

echo "=== STARTING APPLICATION ==="
echo "Environment variables:"
echo "DB_CONNECTION: ${DB_CONNECTION}"
echo "DB_HOST: ${DB_HOST}"
echo "DB_DATABASE: ${DB_DATABASE}"
echo "DB_USERNAME: ${DB_USERNAME}"

# Wait a moment for database connection
sleep 3

echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Checking database connection..."
php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo 'Database connection SUCCESSFUL\n';
} catch (\Exception \$e) {
    echo 'Database connection FAILED: ' . \$e->getMessage() . '\n';
    echo 'Current database config: ' . config('database.default') . '\n';
    echo 'Environment DB_CONNECTION: ' . env('DB_CONNECTION') . '\n';
}
"

echo "Running database migrations..."
php artisan migrate --force

echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=8080