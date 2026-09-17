<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_metode_check;");
        } catch (\Throwable $e) {}

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('metode', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('metode', 50)->nullable()->change();
        });
    }
};
