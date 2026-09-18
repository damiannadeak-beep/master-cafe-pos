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

class VoidOrderTest extends TestCase
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

    private function buatPesananDenganMenu(User $kasir, Menu $menu, int $jumlah = 1): Pesanan
    {
        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id, 'tipe_pesanan' => 'takeaway', 'status' => 'pending',
            'total' => $menu->harga * $jumlah, 'total_hpp' => 0, 'tanggal' => now(),
        ]);
        DetailPesanan::create([
            'id_pesanan' => $pesanan->id, 'id_menu' => $menu->id,
            'jumlah' => $jumlah, 'subtotal' => $menu->harga * $jumlah,
        ]);
        Pembayaran::create([
            'id_pesanan' => $pesanan->id, 'status' => 'unpaid',
            'total_bayar' => $menu->harga * $jumlah,
        ]);
        return $pesanan;
    }

    /** TEST 1: Void berhasil dengan password benar */
    public function test_void_berhasil_dengan_password_benar()
    {
        $kasir = User::factory()->create(['password' => bcrypt('password')]);
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Teh Manis', 'harga' => 5000, 'stok' => 48, 'kategori' => 'minuman', 'is_available' => true]);
        $pesanan = $this->buatPesananDenganMenu($kasir, $menu, 2);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Salah input menu', 'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Pesanan berhasil divoid.']);
        $this->assertSoftDeleted('pesanan', ['id' => $pesanan->id]);
        $this->assertDatabaseHas('void_logs', ['pesanan_id' => $pesanan->id, 'kasir_id' => $kasir->id, 'alasan' => 'Salah input menu']);
    }

    /** TEST 2: Void gagal dengan password salah */
    public function test_void_gagal_dengan_password_salah()
    {
        $kasir = User::factory()->create(['password' => bcrypt('password')]);
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Kopi', 'harga' => 8000, 'stok' => 50, 'kategori' => 'minuman', 'is_available' => true]);
        $pesanan = $this->buatPesananDenganMenu($kasir, $menu);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Batal', 'password' => 'salah_banget',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Password yang dimasukkan salah.']);
        $this->assertEquals(50, $menu->fresh()->stok);
    }

    /** TEST 3: Void gagal jika pesanan sudah completed */
    public function test_void_gagal_jika_pesanan_completed()
    {
        $kasir = User::factory()->create(['password' => bcrypt('password')]);
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Nasi Goreng', 'harga' => 15000, 'stok' => 30, 'kategori' => 'makanan', 'is_available' => true]);
        $pesanan = $this->buatPesananDenganMenu($kasir, $menu);
        $pesanan->update(['status' => 'completed']);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Batal', 'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Pesanan sudah selesai dan tidak dapat divoid.']);
    }

    /** TEST 4: Void berhasil membatalkan pesanan dan soft delete */
    public function test_void_berhasil_membatalkan_pesanan()
    {
        $kasir = User::factory()->create(['password' => bcrypt('password')]);
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Es Jeruk', 'harga' => 7000, 'kategori' => 'minuman', 'is_available' => true]);
        $pesanan = $this->buatPesananDenganMenu($kasir, $menu, 2);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Customer batal', 'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertSoftDeleted('pesanan', ['id' => $pesanan->id]);
        $this->assertEquals('cancelled', Pesanan::withTrashed()->find($pesanan->id)->status);
    }

    /** TEST 5: Pemilik TIDAK bisa void via route kasir (middleware role:kasir) */
    public function test_pemilik_tidak_bisa_void_via_route_kasir()
    {
        $pemilik = User::factory()->create(['password' => bcrypt('password')]);
        $pemilik->assignRole('pemilik');
        $menu = Menu::create(['nama_menu' => 'Roti', 'harga' => 10000, 'stok' => 20, 'kategori' => 'makanan', 'is_available' => true]);
        $pesanan = $this->buatPesananDenganMenu($pemilik, $menu);

        $response = $this->actingAs($pemilik)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Void oleh pemilik', 'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    /** TEST 6: Void gagal jika pesanan sudah dibayar lunas */
    public function test_void_gagal_jika_pesanan_sudah_lunas()
    {
        $kasir = User::factory()->create(['password' => bcrypt('password')]);
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Kopi Susu', 'harga' => 12000, 'stok' => 20, 'kategori' => 'minuman', 'is_available' => true]);

        $pesanan = Pesanan::create([
            'id_kasir' => null, 'tipe_pesanan' => 'dine_in', 'status' => 'pending',
            'total' => $menu->harga, 'total_hpp' => 0, 'tanggal' => now(),
        ]);
        DetailPesanan::create([
            'id_pesanan' => $pesanan->id, 'id_menu' => $menu->id,
            'jumlah' => 1, 'subtotal' => $menu->harga,
        ]);
        Pembayaran::create([
            'id_pesanan' => $pesanan->id, 'status' => 'paid',
            'total_bayar' => $menu->harga,
        ]);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Konsumen minta batal', 'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Pesanan sudah dibayar lunas dan tidak dapat dihapus/divoid.']);
    }

    /** TEST 7: Kasir dapat membatalkan pesanan konsumen yang belum bayar */
    public function test_kasir_dapat_membatalkan_pesanan_konsumen_belum_lunas()
    {
        $kasir = User::factory()->create(['password' => bcrypt('password')]);
        $kasir->assignRole('kasir');
        $menu = Menu::create(['nama_menu' => 'Es Teh', 'harga' => 5000, 'kategori' => 'minuman', 'is_available' => true]);

        $pesanan = Pesanan::create([
            'id_kasir' => null, 'tipe_pesanan' => 'dine_in', 'status' => 'pending',
            'total' => 5000, 'total_hpp' => 0, 'tanggal' => now(),
        ]);
        DetailPesanan::create([
            'id_pesanan' => $pesanan->id, 'id_menu' => $menu->id,
            'jumlah' => 1, 'subtotal' => 5000,
        ]);
        Pembayaran::create([
            'id_pesanan' => $pesanan->id, 'status' => 'unpaid',
            'total_bayar' => 5000,
        ]);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Pelanggan membatalkan menu tambahan', 'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Pesanan berhasil divoid.']);
        $this->assertSoftDeleted('pesanan', ['id' => $pesanan->id]);
    }
}