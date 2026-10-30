<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Menu;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Pembayaran;

class KonsumenOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'konsumen', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
    }

    /** Test 1: Halaman publik katalog dapat diakses dan menampilkan menu */
    public function test_katalog_publik_menampilkan_menu()
    {
        $menu = Menu::create([
            'nama_menu' => 'Kopi Susu Gula Aren',
            'harga' => 18000,
            'stok' => 20,
            'kategori' => 'minuman',
            'is_available' => true,
        ]);

        $response = $this->get('/katalog');
        $response->assertStatus(200);
        $response->assertSee('Kopi Susu Gula Aren');
    }

    /** Test 2: Konsumen melakukan pemesanan (Order Submission) */
    public function test_konsumen_dapat_membuat_pesanan_takeaway()
    {
        $konsumen = User::factory()->create();
        $konsumen->assignRole('konsumen');

        $menu = Menu::create([
            'nama_menu' => 'Mie Goreng Spesial',
            'harga' => 15000,
            'stok' => 10,
            'kategori' => 'makanan',
            'is_available' => true,
        ]);

        $orderData = [
            'tipe_pesanan' => 'takeaway',
            'items' => [
                [
                    'id_menu' => $menu->id,
                    'jumlah' => 2,
                    'catatan' => 'Pedas sedang',
                ]
            ]
        ];

        $response = $this->actingAs($konsumen)->postJson('/konsumen/order/add', $orderData);
        $response->assertStatus(200);
        $this->assertDatabaseHas('pesanan', [
            'id_konsumen' => $konsumen->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'pending',
        ]);
        $this->assertEquals(8, $menu->fresh()->stok);
    }

    /** Test 3: Konsumen dapat melihat tracking pesanan aktif di profil */
    public function test_konsumen_dapat_melihat_pesanan_di_profil()
    {
        $konsumen = User::factory()->create();
        $konsumen->assignRole('konsumen');

        $pesanan = Pesanan::create([
            'id_konsumen' => $konsumen->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'pending',
            'tanggal' => now(),
            'total' => 30000,
        ]);

        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'metode' => 'cash',
            'status' => 'unpaid',
            'total_bayar' => 30000,
        ]);

        $response = $this->actingAs($konsumen)->get('/konsumen/profil');
        $response->assertStatus(200);
        $response->assertSee('Pesanan Sedang Berlangsung');
        $response->assertSee('Belum Lunas');
    }

    /** Test 4: Konsumen dapat memberikan rating untuk pesanan yang completed */
    public function test_konsumen_dapat_memberikan_rating()
    {
        $konsumen = User::factory()->create();
        $konsumen->assignRole('konsumen');

        $pesanan = Pesanan::create([
            'id_konsumen' => $konsumen->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'completed',
            'tanggal' => now(),
            'total' => 25000,
        ]);

        $response = $this->actingAs($konsumen)->post('/konsumen/rating/store', [
            'id_pesanan' => $pesanan->id,
            'rating' => 5,
            'komentar' => 'Makanan sangat lezat dan pelayanan cepat!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ratings', [
            'id_pesanan' => $pesanan->id,
            'rating' => 5,
            'komentar' => 'Makanan sangat lezat dan pelayanan cepat!',
        ]);
    }

    /** Test 5: Konsumen dapat mengakses halaman tracking pesanan */
    public function test_konsumen_dapat_mengakses_halaman_tracking()
    {
        $token = (string) \Illuminate\Support\Str::uuid();
        $pesanan = Pesanan::create([
            'order_token' => $token,
            'tipe_pesanan' => 'takeaway',
            'status' => 'pending',
            'tanggal' => now(),
            'total' => 25000,
            'guest_name' => 'Damian',
        ]);

        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'metode' => 'qris',
            'status' => 'unpaid',
            'total_bayar' => 25000,
        ]);

        $response = $this->get('/tracking/' . $token);
        $response->assertStatus(200);
        $response->assertSee('Progres Pesanan Real-Time');
    }

    /** Test 6: Validasi keamanan nomor telepon takeaway menyaring nomor palsu */
    public function test_validasi_keamanan_nomor_telepon_takeaway()
    {
        $menu = Menu::create([
            'nama_menu' => 'Kopi Validasi',
            'harga' => 10000,
            'stok' => 10,
            'kategori' => 'minuman',
            'is_available' => true,
        ]);

        $basePayload = [
            'tipe_pesanan' => 'takeaway',
            'guest_name' => 'Tamu Uji',
            'items' => [
                ['id_menu' => $menu->id, 'jumlah' => 1]
            ]
        ];

        // 1. Tolak nomor urut palsu (081234567890)
        $resFakeSeq = $this->postJson('/konsumen/order/add', array_merge($basePayload, ['guest_phone' => '081234567890']));
        $resFakeSeq->assertStatus(422);
        $resFakeSeq->assertJsonValidationErrors(['guest_phone']);

        // 2. Tolak nomor kembar berulang (081111111111)
        $resFakeRepeat = $this->postJson('/konsumen/order/add', array_merge($basePayload, ['guest_phone' => '081111111111']));
        $resFakeRepeat->assertStatus(422);
        $resFakeRepeat->assertJsonValidationErrors(['guest_phone']);

        // 3. Tolak prefix operator fiktif (08001234567)
        $resFakePrefix = $this->postJson('/konsumen/order/add', array_merge($basePayload, ['guest_phone' => '080012345678']));
        $resFakePrefix->assertStatus(422);
        $resFakePrefix->assertJsonValidationErrors(['guest_phone']);

        // 4. Terima nomor operator asli Indonesia (081298761234)
        $resValid = $this->postJson('/konsumen/order/add', array_merge($basePayload, ['guest_phone' => '081298761234']));
        $resValid->assertStatus(200);
        $this->assertDatabaseHas('pesanan', [
            'guest_phone' => '081298761234',
            'tipe_pesanan' => 'takeaway',
        ]);
    }
}
