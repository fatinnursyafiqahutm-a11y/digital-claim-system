<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('claim_categories', function (Blueprint $table) {
            $table->json('validation_rules')->nullable()->after('description');

            // Add index for better performance on active categories
            $table->index(['name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claim_categories', function (Blueprint $table) {
            $table->dropIndex(['name', 'is_active']);
            $table->dropColumn('validation_rules');
        });
    }
};