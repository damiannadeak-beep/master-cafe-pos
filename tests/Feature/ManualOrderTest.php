<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Menu;
use App\Models\Bahan;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\Meja;

class ManualOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'konsumen', 'guard_name' => 'web']);
        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);
    }

    private function buatMeja(): Meja
    {
        return Meja::create(['nama_meja_atau_nomor' => 'T1', 'is_available' => true]);
    }

    /** TEST 1: Manual order dine_in berhasil dibuat */
    public function test_manual_order_dine_in_berhasil()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Es Teh', 'harga' => 5000, 'stok' => 50, 'kategori' => 'minuman', 'is_available' => true]);
        $meja = $this->buatMeja();

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id, 'tipe_pesanan' => 'dine_in', 'pembayaran_langsung' => false,
            'items' => [['id_menu' => $menu->id, 'jumlah' => 3]],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pesanan', ['id_kasir' => $kasir->id, 'tipe_pesanan' => 'dine_in', 'status' => 'pending']);
    }

    /** TEST 2: Manual order takeaway berhasil (tetap butuh id_meja) */
    public function test_manual_order_takeaway_berhasil()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Nasi Goreng', 'harga' => 15000, 'kategori' => 'makanan', 'is_available' => true]);
        $meja = $this->buatMeja();

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id, 'tipe_pesanan' => 'takeaway', 'pembayaran_langsung' => false,
            'items' => [['id_menu' => $menu->id, 'jumlah' => 1]],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pesanan', ['id_kasir' => $kasir->id, 'tipe_pesanan' => 'takeaway']);
    }

    /** TEST 3: Order gagal jika status menu tidak tersedia (habis) */
    public function test_order_gagal_jika_menu_tidak_tersedia()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Roti Bakar', 'harga' => 10000, 'kategori' => 'makanan', 'is_available' => false]);
        $meja = $this->buatMeja();

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id, 'tipe_pesanan' => 'dine_in', 'pembayaran_langsung' => false,
            'items' => [['id_menu' => $menu->id, 'jumlah' => 1]],
        ]);

        $response->assertStatus(422);
    }

    /** TEST 4: Order dengan multiple menu berhasil */
    public function test_order_dengan_multiple_menu()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $menu1 = Menu::create(['nama_menu' => 'Es Teh', 'harga' => 5000, 'kategori' => 'minuman', 'is_available' => true]);
        $menu2 = Menu::create(['nama_menu' => 'Nasi Goreng', 'harga' => 15000, 'kategori' => 'makanan', 'is_available' => true]);
        $meja = $this->buatMeja();

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id, 'tipe_pesanan' => 'dine_in', 'pembayaran_langsung' => false,
            'items' => [
                ['id_menu' => $menu1->id, 'jumlah' => 2],
                ['id_menu' => $menu2->id, 'jumlah' => 1],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('detail_pesanan', 2);
    }
}