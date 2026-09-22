<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan database indexes pada kolom-kolom yang sering digunakan dalam WHERE, JOIN, dan ORDER BY
     * untuk meningkatkan performa query ketika data semakin banyak dan banyak pengguna bersamaan.
     */
    public function up(): void
    {
        // Indexes untuk tabel pesanan
        Schema::table('pesanan', function (Blueprint $table) {
            // Kolom status digunakan di hampir semua query (pesanan aktif, completed, cancelled)
            $table->index('status', 'idx_pesanan_status');
            // Lookup pesanan per meja (grouping, tracking dine-in)
            $table->index('id_meja', 'idx_pesanan_id_meja');
            // Lookup pesanan per konsumen
            $table->index('id_konsumen', 'idx_pesanan_id_konsumen');
            // Tracking pesanan tamu via token (digunakan di halaman tracking)
            $table->index('order_token', 'idx_pesanan_order_token');
            // Composite index untuk query pesanan aktif per meja
            $table->index(['id_meja', 'status'], 'idx_pesanan_meja_status');
            // Filter berdasarkan tipe pesanan
            $table->index('tipe_pesanan', 'idx_pesanan_tipe');
            // Pencarian berdasarkan tanggal pembuatan
            $table->index('created_at', 'idx_pesanan_created_at');
        });

        // Indexes untuk tabel pembayaran
        Schema::table('pembayaran', function (Blueprint $table) {
            // Foreign key lookup (JOIN dari pesanan)
            $table->index('id_pesanan', 'idx_pembayaran_id_pesanan');
            // Filter status lunas/belum bayar
            $table->index('status', 'idx_pembayaran_status');
            // Webhook lookup dari Midtrans
            $table->index('midtrans_order_id', 'idx_pembayaran_midtrans_order_id');
            // Composite: lookup pembayaran per pesanan + status
            $table->index(['id_pesanan', 'status'], 'idx_pembayaran_pesanan_status');
        });

        // Indexes untuk tabel detail_pesanan
        Schema::table('detail_pesanan', function (Blueprint $table) {
            // Foreign key lookup (JOIN dari pesanan)
            $table->index('id_pesanan', 'idx_detail_pesanan_id_pesanan');
            // Foreign key lookup (JOIN dari menu)
            $table->index('id_menu', 'idx_detail_pesanan_id_menu');
        });

        // Indexes untuk tabel notifications
        Schema::table('notifications', function (Blueprint $table) {
            // Polling notifikasi belum dibaca
            $table->index('is_read', 'idx_notifications_is_read');
            // Composite: filter notifikasi per tipe + status baca
            $table->index(['type', 'is_read'], 'idx_notifications_type_is_read');
        });

        // Index untuk tabel settings
        Schema::table('settings', function (Blueprint $table) {
            $table->unique('key', 'idx_settings_key_unique');
        });

        // Index untuk tabel kasir_shifts
        Schema::table('kasir_shifts', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_kasir_shifts_user_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropIndex('idx_pesanan_status');
            $table->dropIndex('idx_pesanan_id_meja');
            $table->dropIndex('idx_pesanan_id_konsumen');
            $table->dropIndex('idx_pesanan_order_token');
            $table->dropIndex('idx_pesanan_meja_status');
            $table->dropIndex('idx_pesanan_tipe');
            $table->dropIndex('idx_pesanan_created_at');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex('idx_pembayaran_id_pesanan');
            $table->dropIndex('idx_pembayaran_status');
            $table->dropIndex('idx_pembayaran_midtrans_order_id');
            $table->dropIndex('idx_pembayaran_pesanan_status');
        });

        Schema::table('detail_pesanan', function (Blueprint $table) {
            $table->dropIndex('idx_detail_pesanan_id_pesanan');
            $table->dropIndex('idx_detail_pesanan_id_menu');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_is_read');
            $table->dropIndex('idx_notifications_type_is_read');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('idx_settings_key_unique');
        });

        Schema::table('kasir_shifts', function (Blueprint $table) {
            $table->dropIndex('idx_kasir_shifts_user_status');
        });
    }
};
