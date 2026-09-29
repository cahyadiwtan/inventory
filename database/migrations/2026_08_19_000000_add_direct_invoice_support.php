<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['sales_order_id']);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignUuid('sales_order_id')->nullable()->change();
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['sales_order_id']);
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignUuid('sales_order_id')->nullable()->change();
            $table->foreignUuid('sales_invoice_id')->nullable()->after('sales_order_id')->constrained('sales_invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sales_invoice_id');
            $table->foreignUuid('sales_order_id')->nullable(false)->change();
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->restrictOnDelete();
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignUuid('sales_order_id')->nullable(false)->change();
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->restrictOnDelete();
        });
    }
};