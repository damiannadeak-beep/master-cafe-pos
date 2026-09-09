<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Bahan;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\URL;

class ManagementAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        Role::create(['name' => 'pemilik']);
        Role::create(['name' => 'kasir']);
        Role::create(['name' => 'konsumen']);
    }

    public function test_admin_dapat_mengelola_meja(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        // 1. Admin Store Meja
        $resStore = $this->actingAs($admin)->post('/admin/meja', [
            'nama_meja_atau_nomor' => 'Meja VIP 99',
        ]);
        $resStore->assertRedirect('/admin/meja');
        $this->assertDatabaseHas('meja', ['nama_meja_atau_nomor' => 'Meja VIP 99']);

        $meja = Meja::where('nama_meja_atau_nomor', 'Meja VIP 99')->first();

        // 2. Admin Print QR
        $resQr = $this->actingAs($admin)->get("/admin/meja/{$meja->id}/qr");
        $resQr->assertStatus(200);
        $resQr->assertSee('Meja VIP 99');

        // 3. Admin Update Meja
        $resUpdate = $this->actingAs($admin)->put("/admin/meja/{$meja->id}", [
            'nama_meja_atau_nomor' => 'Meja VIP 100',
        ]);
        $resUpdate->assertRedirect('/admin/meja');
        $this->assertDatabaseHas('meja', ['nama_meja_atau_nomor' => 'Meja VIP 100']);

        // 4. Admin Destroy Meja
        $resDelete = $this->actingAs($admin)->delete("/admin/meja/{$meja->id}");
        $resDelete->assertRedirect('/admin/meja');
        $this->assertDatabaseMissing('meja', ['id' => $meja->id]);
    }

    public function test_admin_dapat_mengelola_bahan_baku(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        // 1. Store Bahan
        $resStore = $this->actingAs($admin)->post('/admin/stok', [
            'nama_bahan' => 'Biji Kopi Arabika Gayo',
            'stok' => 5000,
            'satuan' => 'gram',
            'harga_beli' => 150000,
        ]);
        $resStore->assertStatus(302);
        $this->assertDatabaseHas('bahans', [
            'nama_bahan' => 'Biji Kopi Arabika Gayo',
            'stok' => 5000,
            'satuan' => 'gram',
        ]);

        $bahan = Bahan::where('nama_bahan', 'Biji Kopi Arabika Gayo')->first();

        // 2. Update Bahan
        $resUpdate = $this->actingAs($admin)->put("/admin/stok/{$bahan->id}", [
            'nama_bahan' => 'Biji Kopi Arabika Gayo Premium',
            'stok' => 4500,
            'satuan' => 'gram',
            'harga_beli' => 160000,
        ]);
        $resUpdate->assertStatus(302);
        $this->assertDatabaseHas('bahans', [
            'id' => $bahan->id,
            'nama_bahan' => 'Biji Kopi Arabika Gayo Premium',
            'stok' => 4500,
        ]);

        // 3. Delete Bahan
        $resDelete = $this->actingAs($admin)->delete("/admin/stok/{$bahan->id}");
        $resDelete->assertStatus(302);
        $this->assertDatabaseMissing('bahans', ['id' => $bahan->id]);
    }

    public function test_kasir_dapat_update_stok_opname(): void
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $bahan = Bahan::create([
            'nama_bahan' => 'Susu Full Cream',
            'stok' => 10,
            'satuan' => 'liter',
            'harga_beli' => 20000,
        ]);

        $menu = Menu::create([
            'nama_menu' => 'Kopi Susu Gula Aren',
            'harga' => 18000,
            'stok' => 20,
            'kategori' => 'minuman',
            'is_available' => true,
        ]);

        // Kasir update stok opname
        $response = $this->actingAs($kasir)->post('/kasir/stok', [
            'bahan' => [
                $bahan->id => 15,
            ],
            'menu' => [
                $menu->id => 0,
            ],
            'menu_available' => [
                $menu->id => '0',
            ],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('bahans', [
            'id' => $bahan->id,
            'stok' => 15,
        ]);
        $this->assertDatabaseHas('menu', [
            'id' => $menu->id,
            'stok' => 0,
            'is_available' => false,
        ]);
    }

    public function test_admin_dapat_update_pengaturan_toko_dan_lokasi(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        // 1. Update Profile Toko
        $resProfile = $this->actingAs($admin)->post('/admin/settings/profile', [
            'store_name' => 'Master Cafe & Bistro',
            'store_address' => 'Jl. Merdeka No. 45',
            'store_phone' => '081234567890',
            'receipt_footer' => 'Terima kasih atas kunjungan Anda!',
        ]);
        $resProfile->assertRedirect('/admin/settings');

        $this->assertEquals('Master Cafe & Bistro', Setting::getVal('store_name'));
        $this->assertEquals('Jl. Merdeka No. 45', Setting::getVal('store_address'));

        // 2. Update Pengaturan Lokasi
        $resLokasi = $this->actingAs($admin)->post('/admin/settings/lokasi', [
            'lokasi_judul' => 'Kunjungi Outlet Kami',
            'lokasi_deskripsi' => 'Nikmati kopi terbaik di pusat kota.',
            'lokasi_utama_nama' => 'Master Cafe Downtown',
            'lokasi_utama_alamat' => 'Jl. Thamrin No. 10',
            'lokasi_jam_operasional' => '09:00 - 23:00',
        ]);
        $resLokasi->assertRedirect('/admin/settings');

        $this->assertEquals('Master Cafe Downtown', Setting::getVal('lokasi_utama_nama'));
    }

    public function test_kasir_dapat_mengakses_api_notifikasi_dan_active_orders_count(): void
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $meja = Meja::create(['nama_meja_atau_nomor' => 'Meja 01']);

        // Create pesanan aktif
        $pesanan = Pesanan::create([
            'id_meja' => $meja->id,
            'tipe_pesanan' => 'dine_in',
            'tanggal' => now(),
            'status' => 'pending',
            'total' => 50000,
        ]);
        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'status' => 'unpaid',
            'total_bayar' => 50000,
        ]);

        // Create notification
        $notif = Notification::create([
            'type' => 'call_bell',
            'message' => 'Panggilan Meja 01',
            'id_meja' => $meja->id,
            'is_read' => false,
        ]);

        // 1. Check active orders count API
        $resCount = $this->actingAs($kasir)->getJson('/kasir/api/active-orders-count');
        $resCount->assertStatus(200);
        $resCount->assertJsonPath('count', 1);

        // 2. Check notifications API
        $resNotif = $this->actingAs($kasir)->getJson('/kasir/api/notifications');
        $resNotif->assertStatus(200);
        $resNotif->assertJsonFragment(['message' => 'Panggilan Meja 01']);

        // 3. Mark notification as read
        $resRead = $this->actingAs($kasir)->postJson("/kasir/api/notifications/{$notif->id}/read");
        $resRead->assertStatus(200);
        $this->assertDatabaseHas('notifications', [
            'id' => $notif->id,
            'is_read' => true,
        ]);
    }

    public function test_konsumen_dapat_membatalkan_pesanan_pending(): void
    {
        $konsumen = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $konsumen->assignRole('konsumen');

        $pesanan = Pesanan::create([
            'id_konsumen' => $konsumen->id,
            'tipe_pesanan' => 'takeaway',
            'tanggal' => now(),
            'status' => 'pending',
            'total' => 25000,
        ]);
        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'status' => 'unpaid',
            'total_bayar' => 25000,
        ]);

        // Konsumen cancel pesanan
        $response = $this->actingAs($konsumen)->postJson("/konsumen/order/{$pesanan->id}/cancel");
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Pesanan berhasil dibatalkan.']);

        $this->assertSoftDeleted('pesanan', ['id' => $pesanan->id]);
    }
}
