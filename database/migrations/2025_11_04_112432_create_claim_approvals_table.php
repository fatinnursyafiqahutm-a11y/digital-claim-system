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
        Schema::create('claim_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->onDelete('cascade');
            $table->foreignId('approved_by')->constrained('users')->onDelete('restrict');
            $table->enum('action', ['approved', 'rejected', 'returned_for_info']);
            $table->string('previous_status', 50);
            $table->string('new_status', 50);
            $table->text('comments')->nullable();
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['claim_id']);
            $table->index(['approved_by']);
            $table->index(['action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_approvals');
    }
};
