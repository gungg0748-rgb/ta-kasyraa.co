<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan data pembayaran kasir: metode, uang dibayar, dan kembalian.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_method')->default('tunai')->after('total');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_method');
            $table->decimal('change_amount', 15, 2)->default(0)->after('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'paid_amount', 'change_amount']);
        });
    }
};
