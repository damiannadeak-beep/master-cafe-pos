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
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->decimal('uang_diterima', 12, 2)->nullable()->after('total_bayar');
            $table->decimal('uang_kembalian', 12, 2)->nullable()->after('uang_diterima');
            $table->string('catatan_kembalian')->nullable()->after('uang_kembalian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['uang_diterima', 'uang_kembalian', 'catatan_kembalian']);
        });
    }
};
