<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 50);
            $table->string('title');
            $table->text('message');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 36)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'is_read']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->index(['status', 'valid_until']);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->index(['status', 'due_date']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->index(['status', 'due_date']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['movement_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['movement_type', 'created_at']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex(['status', 'valid_until']);
        });

        Schema::dropIfExists('notifications');
    }
};
