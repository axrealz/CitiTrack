<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds purchase-cost and supplier-payment details to stock-in records.
 *
 * SAFE: only ADDS nullable columns to stock_logs. No existing rows or columns
 * are changed, so old stock-in / sale / return / adjustment history stays valid
 * (the new columns are simply NULL for old rows).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->date('purchase_date')->nullable()->after('unit_cost');
            $table->decimal('total_cost', 12, 2)->nullable()->after('purchase_date');
            $table->decimal('amount_paid', 12, 2)->nullable()->after('total_cost');
            $table->string('payment_method', 20)->nullable()->after('amount_paid');
            $table->string('payment_status', 20)->nullable()->after('payment_method');
            $table->string('reference_no', 100)->nullable()->after('payment_status');

            // One-time token per form: a unique value makes the database itself
            // refuse a duplicate submission (NULLs are allowed many times).
            $table->uuid('submission_token')->nullable()->unique()->after('reference_no');

            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropUnique(['submission_token']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn([
                'purchase_date',
                'total_cost',
                'amount_paid',
                'payment_method',
                'payment_status',
                'reference_no',
                'submission_token',
            ]);
        });
    }
};