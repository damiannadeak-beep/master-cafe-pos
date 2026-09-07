<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Menu;
use App\Models\Bahan;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Pembayaran;

class PesananModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'konsumen', 'guard_name' => 'web']);
    }

    /**
     * TEST 1: restoreStock() mengembalikan stok menu dengan benar
     */
    public function test_restore_stock_mengembalikan_stok_menu()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $menu = Menu::create([
            'nama_menu' => 'Es Teh',
            'harga' => 5000,
            'stok' => 8,
            'kategori' => 'minuman',
            'is_available' => true,
        ]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'pending',
            'total' => 10000,
            'total_hpp' => 0,
            'tanggal' => now(),
        ]);

        DetailPesanan::create([
            'id_pesanan' => $pesanan->id,
            'id_menu' => $menu->id,
            'jumlah' => 2,
            'subtotal' => 10000,
        ]);

        $pesanan->restoreStock();

        $this->assertEquals(10, $menu->fresh()->stok);
    }

    /**
     * TEST 2: restoreStock() mengembalikan stok bahan baku
     */
    public function test_restore_stock_mengembalikan_stok_bahan_baku()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $bahan = Bahan::create([
            'nama_bahan' => 'Teh Celup',
            'satuan' => 'pcs',
            'stok' => 97,
            'harga_beli' => 1000,
        ]);

        $menu = Menu::create([
            'nama_menu' => 'Teh Hangat',
            'harga' => 5000,
            'stok' => 47,
            'kategori' => 'minuman',
            'is_available' => true,
        ]);

        $menu->bahans()->attach($bahan->id, ['jumlah_dibutuhkan' => 1]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'dine_in',
            'status' => 'pending',
            'total' => 15000,
            'total_hpp' => 0,
            'tanggal' => now(),
        ]);

        DetailPesanan::create([
            'id_pesanan' => $pesanan->id,
            'id_menu' => $menu->id,
            'jumlah' => 3,
            'subtotal' => 15000,
        ]);

        $pesanan->restoreStock();

        $this->assertEquals(100, $bahan->fresh()->stok);
        $this->assertEquals(50, $menu->fresh()->stok);
    }

    /**
     * TEST 3: cancelOrder() membatalkan pesanan dan soft delete
     */
    public function test_cancel_order_mengubah_status_dan_soft_delete()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $menu = Menu::create([
            'nama_menu' => 'Nasi Goreng',
            'harga' => 15000,
            'stok' => 18,
            'kategori' => 'makanan',
            'is_available' => true,
        ]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'dine_in',
            'status' => 'pending',
            'total' => 30000,
            'total_hpp' => 0,
            'tanggal' => now(),
        ]);

        DetailPesanan::create([
            'id_pesanan' => $pesanan->id,
            'id_menu' => $menu->id,
            'jumlah' => 2,
            'subtotal' => 30000,
        ]);

        $pesanan->cancelOrder();

        $this->assertDatabaseHas('pesanan', [
            'id' => $pesanan->id,
            'status' => 'cancelled',
        ]);
        $this->assertSoftDeleted('pesanan', ['id' => $pesanan->id]);
        $this->assertEquals(20, $menu->fresh()->stok);
    }

    /**
     * TEST 4: cancelOrder() menghapus record pembayaran
     */
    public function test_cancel_order_menghapus_pembayaran()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $menu = Menu::create([
            'nama_menu' => 'Mie Goreng',
            'harga' => 12000,
            'stok' => 30,
            'kategori' => 'makanan',
            'is_available' => true,
        ]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'pending',
            'total' => 12000,
            'total_hpp' => 0,
            'tanggal' => now(),
        ]);

        DetailPesanan::create([
            'id_pesanan' => $pesanan->id,
            'id_menu' => $menu->id,
            'jumlah' => 1,
            'subtotal' => 12000,
        ]);

        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'status' => 'unpaid',
            'total_bayar' => 12000,
        ]);

        $pesanan->cancelOrder();

        $this->assertDatabaseMissing('pembayaran', [
            'id_pesanan' => $pesanan->id,
        ]);
    }

    /**
     * TEST 5: cancelOrder() tidak berjalan dua kali
     */
    public function test_cancel_order_tidak_berjalan_dua_kali()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $menu = Menu::create([
            'nama_menu' => 'Kopi',
            'harga' => 8000,
            'stok' => 10,
            'kategori' => 'minuman',
            'is_available' => true,
        ]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'cancelled',
            'total' => 8000,
            'total_hpp' => 0,
            'tanggal' => now(),
        ]);

        DetailPesanan::create([
            'id_pesanan' => $pesanan->id,
            'id_menu' => $menu->id,
            'jumlah' => 1,
            'subtotal' => 8000,
        ]);

        $pesanan->cancelOrder();

        $this->assertEquals(10, $menu->fresh()->stok);
    }
}