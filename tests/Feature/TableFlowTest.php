<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Meja;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class TableFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $konsumen;
    protected $kasir;
    protected $meja;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        Role::create(['name' => 'konsumen', 'guard_name' => 'web']);

        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);

        $this->konsumen = User::factory()->create();
        $this->konsumen->assignRole('konsumen');

        $this->kasir = User::factory()->create();
        $this->kasir->assignRole('kasir');

        $this->meja = Meja::create([
            'nama_meja_atau_nomor' => 'Meja Test 99',
            'is_available' => true
        ]);
    }

    /**
     * TEST 1: Dari web biasa (tanpa scan QR), akses /konsumen/menu harus dialihkan
     * dan tidak boleh menampilkan daftar pilih meja.
     */
    public function test_web_biasa_tidak_bisa_pilih_meja_tanpa_scan_qr()
    {
        $response = $this->actingAs($this->konsumen)->get('/konsumen/menu');

        $response->assertStatus(302);
        $response->assertRedirect(route('pilih_tipe'));
        $response->assertSessionHas('info');
    }

    /**
     * TEST 2: Halaman pilih-tipe menampilkan modal panduan scan QR saat klik Makan di Tempat.
     */
    public function test_halaman_pilih_tipe_memiliki_modal_panduan_scan_qr()
    {
        $response = $this->actingAs($this->konsumen)->get('/konsumen/pilih-tipe');

        $response->assertStatus(200);
        $response->assertSee('modalDineInInfo');
        $response->assertSee('Makan di Tempat (Dine-In)');
        $response->assertSee('pindai (scan) stiker QR');
    }

    /**
     * TEST 3: Scan QR fisik (Signed URL) langsung membuka menu meja tanpa pop-up peringatan.
     */
    public function test_scan_qr_meja_langsung_buka_menu_tanpa_popup_konfirmasi()
    {
        $signedUrl = URL::signedRoute('konsumen.menu.meja', ['id_meja' => $this->meja->id]);

        $response = $this->actingAs($this->konsumen)->get($signedUrl);

        $response->assertStatus(200);
        $response->assertSee($this->meja->nama_meja_atau_nomor);
        // Pastikan view konfirmasi_meja tidak muncul
        $response->assertDontSee('Meja Sedang Digunakan');
        $response->assertDontSee('Apakah Anda berada di rombongan yang sama');
    }

    /**
     * TEST 4: Kasir membuka halaman monitor meja, tidak ada saklar switch manual,
     * melainkan kartu live monitor meja.
     */
    public function test_kasir_meja_menampilkan_live_monitor_tanpa_saklar_manual()
    {
        $response = $this->actingAs($this->kasir)->get('/kasir/meja');

        $response->assertStatus(200);
        $response->assertSee('Live Monitor Meja');
        $response->assertSee('Total Meja Terdaftar');
        $response->assertSee('Meja Sedang Ada Pesanan');
        $response->assertSee('Meja Tersedia / Bersih');
        // Pastikan saklar on/off switch sudah tidak ada
        $response->assertDontSee('form-switch');
        $response->assertDontSee('toggleMeja');
    }
}
