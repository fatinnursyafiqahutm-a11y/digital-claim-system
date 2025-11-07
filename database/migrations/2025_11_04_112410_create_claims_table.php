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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('claim_categories')->onDelete('restrict');
            $table->string('title', 255);
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MYR');
            $table->date('claim_date');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'paid'])->default('draft');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            // Performance indexes
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['claim_date']);
            $table->index(['status', 'submitted_at']);
            $table->index(['category_id', 'status']);
            $table->index(['priority', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
