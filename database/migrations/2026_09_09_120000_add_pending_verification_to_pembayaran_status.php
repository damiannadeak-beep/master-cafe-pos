<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_status_check;");
            DB::statement("ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_status_check CHECK (status IN ('unpaid', 'paid', 'pending_verification'));");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE pembayaran MODIFY COLUMN status ENUM('unpaid', 'paid', 'pending_verification') DEFAULT 'unpaid';");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE pembayaran DROP CONSTRAINT IF EXISTS pembayaran_status_check;");
            DB::statement("ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_status_check CHECK (status IN ('unpaid', 'paid'));");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE pembayaran MODIFY COLUMN status ENUM('unpaid', 'paid') DEFAULT 'unpaid';");
        }
    }
};
