<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

// Create a new Laravel application instance
$app = new Container();

// Set up the database connection
$capsule = new Capsule($app);
$capsule->addConnection([
    'driver'    => 'sqlite',
    'database'  => __DIR__ . '/database/database.sqlite',
    'prefix'    => '',
]);

$capsule->setEventDispatcher(new Dispatcher($app));
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "Exporting SQLite data...\n";

// Export all tables to SQL format
$tables = ['users', 'roles', 'permissions', 'claim_categories', 'claims', 'receipts', 'claim_approvals', 'audit_logs', 'settings', 'migrations', 'sessions', 'cache'];

$output = "-- SQLite to PostgreSQL Export\n";
$output .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tables as $table) {
    try {
        $data = Capsule::table($table)->get();

        if ($data->isEmpty()) {
            echo "Table '$table' is empty, skipping...\n";
            continue;
        }

        echo "Exporting table '$table' (" . $data->count() . " records)...\n";

        $output .= "-- Data for table: $table\n";

        foreach ($data as $row) {
            $columns = [];
            $values = [];

            foreach ($row as $key => $value) {
                $columns[] = $key;

                if ($value === null) {
                    $values[] = 'NULL';
                } elseif (is_string($value)) {
                    // Escape single quotes and handle special characters
                    $escapedValue = str_replace("'", "''", $value);
                    $values[] = "'$escapedValue'";
                } elseif (is_bool($value)) {
                    $values[] = $value ? 'true' : 'false';
                } elseif (is_int($value) || is_float($value)) {
                    $values[] = $value;
                } else {
                    // Handle JSON, arrays, etc.
                    $jsonValue = json_encode($value);
                    $escapedJson = str_replace("'", "''", $jsonValue);
                    $values[] = "'$escapedJson'";
                }
            }

            $columnsStr = implode(', ', $columns);
            $valuesStr = implode(', ', $values);

            $output .= "INSERT INTO $table ($columnsStr) VALUES ($valuesStr);\n";
        }

        $output .= "\n";

    } catch (Exception $e) {
        echo "Error exporting table '$table': " . $e->getMessage() . "\n";
    }
}

// Save the export
file_put_contents(__DIR__ . '/database_export.sql', $output);

echo "\nExport completed! Data saved to 'database_export.sql'\n";
echo "You can now import this file into your Supabase database.\n";