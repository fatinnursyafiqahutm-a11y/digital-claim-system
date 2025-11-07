#!/bin/sh

# Wait a moment for database connection
sleep 5

# Clear all caches again to ensure environment variables are loaded
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Check database connection
php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo 'Database connection successful\n';
} catch (\Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage() . '\n';
    echo 'Current database config: ' . json_encode(config('database.default')) . '\n';
    echo 'Environment DB_CONNECTION: ' . env('DB_CONNECTION') . '\n';
}
"

# Run database migrations
php artisan migrate --force

# Start the Laravel server
php artisan serve --host=0.0.0.0 --port=8080