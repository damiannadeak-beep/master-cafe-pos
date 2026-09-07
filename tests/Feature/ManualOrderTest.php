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
        $this->assertEquals(47, $menu->fresh()->stok);
    }

    /** TEST 2: Manual order takeaway berhasil (tetap butuh id_meja) */
    public function test_manual_order_takeaway_berhasil()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Nasi Goreng', 'harga' => 15000, 'stok' => 20, 'kategori' => 'makanan', 'is_available' => true]);
        $meja = $this->buatMeja();

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id, 'tipe_pesanan' => 'takeaway', 'pembayaran_langsung' => false,
            'items' => [['id_menu' => $menu->id, 'jumlah' => 1]],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pesanan', ['id_kasir' => $kasir->id, 'tipe_pesanan' => 'takeaway']);
        $this->assertEquals(19, $menu->fresh()->stok);
    }

    /** TEST 3: Stok bahan baku ikut berkurang saat order */
    public function test_stok_bahan_berkurang_saat_order()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $bahan = Bahan::create(['nama_bahan' => 'Kopi Bubuk', 'satuan' => 'gram', 'stok' => 1000, 'harga_beli' => 50000]);
        $menu = Menu::create(['nama_menu' => 'Kopi Hitam', 'harga' => 10000, 'stok' => 100, 'kategori' => 'minuman', 'is_available' => true]);
        $menu->bahans()->attach($bahan->id, ['jumlah_dibutuhkan' => 20]);
        $meja = $this->buatMeja();

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id, 'tipe_pesanan' => 'dine_in', 'pembayaran_langsung' => false,
            'items' => [['id_menu' => $menu->id, 'jumlah' => 2]],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(960, $bahan->fresh()->stok);
    }

    /** TEST 4: Order gagal jika stok menu habis */
    public function test_order_gagal_jika_stok_habis()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Roti Bakar', 'harga' => 10000, 'stok' => 1, 'kategori' => 'makanan', 'is_available' => true]);
        $meja = $this->buatMeja();

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id, 'tipe_pesanan' => 'dine_in', 'pembayaran_langsung' => false,
            'items' => [['id_menu' => $menu->id, 'jumlah' => 5]],
        ]);

        $response->assertStatus(422);
    }

    /** TEST 5: Order dengan multiple menu */
    public function test_order_dengan_multiple_menu()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $menu1 = Menu::create(['nama_menu' => 'Es Teh', 'harga' => 5000, 'stok' => 50, 'kategori' => 'minuman', 'is_available' => true]);
        $menu2 = Menu::create(['nama_menu' => 'Nasi Goreng', 'harga' => 15000, 'stok' => 30, 'kategori' => 'makanan', 'is_available' => true]);
        $meja = $this->buatMeja();

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id, 'tipe_pesanan' => 'dine_in', 'pembayaran_langsung' => false,
            'items' => [
                ['id_menu' => $menu1->id, 'jumlah' => 2],
                ['id_menu' => $menu2->id, 'jumlah' => 1],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertEquals(48, $menu1->fresh()->stok);
        $this->assertEquals(29, $menu2->fresh()->stok);
    }
}