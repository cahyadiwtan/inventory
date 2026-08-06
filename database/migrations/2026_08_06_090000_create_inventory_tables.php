<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 50)->unique();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('type', 10);
            $table->text('reason');
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('qty', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 50)->unique();
            $table->foreignUuid('from_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignUuid('to_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('status', 20)->default('requested');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('qty', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 50)->unique();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->date('opname_date');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('system_qty', 16, 2)->default(0);
            $table->decimal('actual_qty', 16, 2)->default(0);
            $table->decimal('difference', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('movement_type', 10);
            $table->string('reference_type')->nullable();
            $table->string('reference_id', 36)->nullable();
            $table->decimal('qty', 16, 2)->default(0);
            $table->decimal('qty_before', 16, 2)->default(0);
            $table->decimal('qty_after', 16, 2)->default(0);
            $table->string('reason')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('approvable_type');
            $table->string('approvable_id', 36);
            $table->foreignUuid('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 20);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['approvable_type', 'approvable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
    }
};
