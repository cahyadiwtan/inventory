<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 50)->unique();
            $table->foreignUuid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->date('quotation_date');
            $table->date('valid_until');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->string('description')->nullable();
            $table->decimal('qty', 16, 2)->default(0);
            $table->decimal('unit_price', 16, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->foreignUuid('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('line_total', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 50)->unique();
            $table->foreignUuid('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->date('order_date');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignUuid('quotation_item_id')->nullable()->constrained('quotation_items')->nullOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->string('description')->nullable();
            $table->decimal('qty', 16, 2)->default(0);
            $table->decimal('unit_price', 16, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->foreignUuid('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('line_total', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 50)->unique();
            $table->foreignUuid('sales_order_id')->constrained('sales_orders')->restrictOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->date('delivery_date');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->foreignUuid('sales_order_item_id')->nullable()->constrained('sales_order_items')->nullOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('qty', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 50)->unique();
            $table->foreignUuid('sales_order_id')->constrained('sales_orders')->restrictOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('paid_amount', 16, 2)->default(0);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->string('description')->nullable();
            $table->decimal('qty', 16, 2)->default(0);
            $table->decimal('unit_price', 16, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->foreignUuid('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('line_total', 16, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 50)->unique();
            $table->string('payable_type');
            $table->string('payable_id', 36);
            $table->date('payment_date');
            $table->string('payment_method', 20)->default('cash');
            $table->string('reference')->nullable();
            $table->decimal('amount', 16, 2)->default(0);
            $table->string('status', 20)->default('posted');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
