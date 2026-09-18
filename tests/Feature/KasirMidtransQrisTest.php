<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Menu;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KasirMidtransQrisTest extends TestCase
{
    use RefreshDatabase;

    protected $kasir;
    protected $meja;
    protected $menu;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'kasir']);
        $this->kasir = User::factory()->create();
        $this->kasir->assignRole('kasir');

        $this->meja = Meja::create([
            'nama_meja_atau_nomor' => 'Meja 1',
            'is_available' => true,
        ]);

        $this->menu = Menu::create([
            'nama_menu' => 'Kopi Latte',
            'kategori' => 'minuman',
            'harga' => 20000,
            'is_available' => true,
        ]);
    }

    /** Test 1: Kasir dapat generate Snap Token untuk pesanan manual QRIS */
    public function test_kasir_dapat_membuat_order_manual_qris_dan_menerima_snap_token()
    {
        $response = $this->actingAs($this->kasir)->postJson('/kasir/manual-order', [
            'tipe_pesanan' => 'dine_in',
            'id_meja' => $this->meja->id,
            'pembayaran_langsung' => false,
            'metode_pembayaran' => 'qris',
            'items' => [
                [
                    'id_menu' => $this->menu->id,
                    'jumlah' => 1,
                    'harga' => 20000,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'id_pesanan',
            'snap_token',
            'client_key',
            'is_production'
        ]);

        $pesananId = $response->json('id_pesanan');
        $this->assertDatabaseHas('pesanan', [
            'id' => $pesananId,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('pembayaran', [
            'id_pesanan' => $pesananId,
            'status' => 'unpaid',
            'metode' => 'qris',
        ]);
    }

    /** Test 2: Endpoint snap-token mengembalikan data token transaksi */
    public function test_kasir_dapat_mengambil_snap_token_pesanan_aktif()
    {
        $pesanan = Pesanan::create([
            'id_kasir' => $this->kasir->id,
            'id_meja' => $this->meja->id,
            'tipe_pesanan' => 'dine_in',
            'tanggal' => now(),
            'status' => 'pending',
            'total' => 20000,
        ]);

        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'metode' => 'qris',
            'status' => 'unpaid',
            'total_bayar' => 20000,
        ]);

        $response = $this->actingAs($this->kasir)->postJson("/kasir/order/{$pesanan->id}/snap-token");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'snap_token',
            'client_key',
            'midtrans_order_id',
            'total_bayar'
        ]);
    }

    /** Test 3: Endpoint qris-success menandai pesanan lunas dan membebaskan meja */
    public function test_qris_success_menandai_pesanan_lunas()
    {
        $pesanan = Pesanan::create([
            'id_kasir' => $this->kasir->id,
            'id_meja' => $this->meja->id,
            'tipe_pesanan' => 'dine_in',
            'tanggal' => now(),
            'status' => 'pending',
            'total' => 20000,
        ]);

        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'metode' => 'qris',
            'status' => 'unpaid',
            'total_bayar' => 20000,
        ]);

        $response = $this->actingAs($this->kasir)->postJson("/kasir/order/{$pesanan->id}/qris-success", [
            'midtrans_result' => [
                'transaction_status' => 'settlement',
                'payment_type' => 'qris'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'id_pesanan' => $pesanan->id
        ]);

        $this->assertEquals('paid', $pesanan->fresh()->pembayaran->status);
        $this->assertEquals('processing', $pesanan->fresh()->status);
    }
}
