<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Menu;
use App\Models\Bahan;
use App\Models\Pesanan;
use App\Models\Meja;

class PosBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles karena RefreshDatabase menghapus semua tabel termasuk roles
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'konsumen', 'guard_name' => 'web']);

        // Bypass EnsureShiftOpen middleware dalam test karena tidak ada shift aktif
        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);
    }

    public function test_pesanan_berhasil_dibuat_saat_menu_tersedia()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $menu = Menu::create([
            'nama_menu' => 'Kopi Hitam',
            'harga' => 10000,
            'kategori' => 'minuman',
            'is_available' => true
        ]);

        $meja = Meja::create([
            'nama_meja_atau_nomor' => '1',
            'is_available' => true
        ]);

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id,
            'tipe_pesanan' => 'dine_in',
            'pembayaran_langsung' => false,
            'items' => [
                [
                    'id_menu' => $menu->id,
                    'jumlah' => 2
                ]
            ]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pesanan', [
            'id_kasir' => $kasir->id,
            'id_meja' => $meja->id,
            'status' => 'pending'
        ]);
        $this->assertFalse((bool) $meja->fresh()->is_available);
    }

    public function test_pesanan_bisa_divoid_dan_status_dibatalkan()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $menu = Menu::create([
            'nama_menu' => 'Teh Manis',
            'harga' => 5000,
            'kategori' => 'minuman',
            'is_available' => true
        ]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'pending',
            'total' => 5000,
            'total_hpp' => 0,
            'tanggal' => now()
        ]);

        $pesanan->detail_pesanan()->create([
            'id_menu' => $menu->id,
            'jumlah' => 1,
            'subtotal' => 5000
        ]);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Salah input',
            'password' => 'password' // password default user factory
        ]);

        $response->assertStatus(200, 'Void order failed: ' . json_encode($response->json()));

        $this->assertSoftDeleted('pesanan', [
            'id' => $pesanan->id
        ]);
        $this->assertEquals('cancelled', Pesanan::withTrashed()->find($pesanan->id)->status);
    }
}
