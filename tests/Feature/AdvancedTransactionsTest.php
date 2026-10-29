<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Menu;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Pembayaran;

class AdvancedTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'konsumen', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);
    }

    /** Test 1: Split Bill memisahkan pesanan menjadi 2 tagihan */
    public function test_split_bill_berhasil_memisahkan_tagihan()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $menu1 = Menu::create(['nama_menu' => 'Nasi Goreng', 'harga' => 20000, 'stok' => 50, 'kategori' => 'makanan', 'is_available' => true]);
        $menu2 = Menu::create(['nama_menu' => 'Es Teh Manis', 'harga' => 5000, 'stok' => 50, 'kategori' => 'minuman', 'is_available' => true]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'dine_in',
            'status' => 'pending',
            'tanggal' => now(),
            'total' => 25000,
            'total_hpp' => 15000,
        ]);

        $pembayaran = Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'status' => 'unpaid',
            'total_bayar' => 25000,
        ]);

        $detail1 = DetailPesanan::create([
            'id_pesanan' => $pesanan->id,
            'id_menu' => $menu1->id,
            'jumlah' => 1,
            'subtotal' => 20000,
        ]);

        $detail2 = DetailPesanan::create([
            'id_pesanan' => $pesanan->id,
            'id_menu' => $menu2->id,
            'jumlah' => 1,
            'subtotal' => 5000,
        ]);

        // Split menu2 (Es Teh Manis) keluar ke pesanan baru
        $response = $this->actingAs($kasir)->postJson("/kasir/order/{$pesanan->id}/split", [
            'split_items' => [
                ['id_detail' => $detail2->id, 'jumlah' => 1]
            ]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('pesanan', 2);
        // Pesanan asli sisa Nasi Goreng (20.000)
        $this->assertEquals(20000, $pesanan->fresh()->total);
    }

    /** Test 2: Konsumen menekan tombol Panggil Pelayan (Call Bell) */
    public function test_konsumen_dapat_memanggil_pelayan_dengan_call_bell()
    {
        $konsumen = User::factory()->create();
        $konsumen->assignRole('konsumen');

        $meja = Meja::create(['nama_meja_atau_nomor' => 'Meja 5', 'is_available' => false]);

        $response = $this->actingAs($konsumen)->postJson('/konsumen/call-bell', [
            'id_meja' => $meja->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Pelayan telah dipanggil dan segera menuju meja Anda.']);
    }
}
