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
        Schema::table('menu', function (Blueprint $table) {
            // Ubah enum jadi string biasa
            $table->string('kategori', 100)->default('makanan')->change();
            // Tambah flag harga dinamis
            $table->boolean('is_dynamic_price')->default(false)->after('harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->dropColumn('is_dynamic_price');
            // Kembalikan ke enum (ini bisa fail di sqlite, tapi aman di mysql)
            DB::statement("ALTER TABLE menu MODIFY kategori ENUM('makanan', 'minuman') DEFAULT 'makanan'");
        });
    }
};
