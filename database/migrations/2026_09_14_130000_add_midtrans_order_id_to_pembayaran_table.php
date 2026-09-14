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
        if (!Schema::hasColumn('pembayaran', 'midtrans_order_id')) {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->string('midtrans_order_id', 100)->nullable()->after('snap_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pembayaran', 'midtrans_order_id')) {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->dropColumn('midtrans_order_id');
            });
        }
    }
};
