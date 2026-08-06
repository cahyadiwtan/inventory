<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('npwp', 30)->nullable();
            $table->text('address');
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('pic_name', 100)->nullable();
            $table->integer('payment_term_days')->default(30);
            $table->decimal('credit_limit', 16, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('npwp', 30)->nullable();
            $table->text('address');
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('pic_name', 100)->nullable();
            $table->integer('payment_term_days')->default(30);
            $table->decimal('credit_limit', 16, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('barcode', 100)->nullable();
            $table->string('name', 150);
            $table->foreignUuid('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->foreignUuid('brand_id')->nullable()->constrained('product_brands')->nullOnDelete();
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('selling_price', 16, 2)->default(0);
            $table->decimal('purchase_price', 16, 2)->default(0);
            $table->decimal('min_stock', 16, 2)->default(0);
            $table->decimal('max_stock', 16, 2)->default(0);
            $table->decimal('reorder_point', 16, 2)->default(0);
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('qty_on_hand', 16, 2)->default(0);
            $table->unique(['product_id', 'warehouse_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('barcode', 100)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('price_type', 20)->default('selling');
            $table->decimal('price', 16, 2)->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('product_barcodes');
        Schema::dropIfExists('product_warehouses');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};
