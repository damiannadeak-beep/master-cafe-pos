<?php

use Database\Seeders\MasterCafeFullMenuSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan seeder seluruh menu fisik Master Cafe
        $seeder = new MasterCafeFullMenuSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu menghapus data menu saat rollback migration ringan
    }
};
