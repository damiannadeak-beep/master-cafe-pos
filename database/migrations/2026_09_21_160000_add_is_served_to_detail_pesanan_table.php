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
        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_pesanan', 'is_served')) {
                $table->boolean('is_served')->default(false)->after('catatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('detail_pesanan', 'is_served')) {
                $table->dropColumn('is_served');
            }
        });
    }
};
