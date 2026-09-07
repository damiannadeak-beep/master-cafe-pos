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

class AuthRoleTest extends TestCase
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

    /** TEST 1: Kasir dapat mengakses halaman POS */
    public function test_kasir_bisa_akses_halaman_pos()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $response = $this->actingAs($kasir)->get('/kasir/pos');
        $response->assertStatus(200);
    }

    /** TEST 2: Konsumen TIDAK bisa mengakses halaman POS */
    public function test_konsumen_tidak_bisa_akses_halaman_pos()
    {
        $konsumen = User::factory()->create();
        $konsumen->assignRole('konsumen');
        $response = $this->actingAs($konsumen)->get('/kasir/pos');
        $response->assertStatus(403);
    }

    /** TEST 3: Guest diarahkan ke login */
    public function test_guest_diarahkan_ke_login()
    {
        $response = $this->get('/kasir/pos');
        $response->assertRedirect('/login');
    }

    /** TEST 4: Kasir bisa mengakses halaman pesanan aktif */
    public function test_kasir_bisa_akses_pesanan_aktif()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $response = $this->actingAs($kasir)->get('/kasir/pesanan-aktif');
        $response->assertStatus(200);
    }

    /** TEST 5: Pemilik TIDAK bisa mengakses route kasir (role middleware) */
    public function test_pemilik_tidak_bisa_akses_route_kasir()
    {
        $pemilik = User::factory()->create();
        $pemilik->assignRole('pemilik');
        $response = $this->actingAs($pemilik)->get('/kasir/pos');
        $response->assertStatus(403);
    }

    /** TEST 6: Login berhasil dengan kredensial benar */
    public function test_login_berhasil_dengan_kredensial_benar()
    {
        $user = User::factory()->create([
            'email' => 'kasir@test.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('kasir');
        $response = $this->post('/login', [
            'email' => 'kasir@test.com',
            'password' => 'password123',
        ]);
        $this->assertAuthenticated();
    }

    /** TEST 7: Login gagal dengan password salah */
    public function test_login_gagal_dengan_password_salah()
    {
        $user = User::factory()->create([
            'email' => 'kasir@test.com',
            'password' => bcrypt('password123'),
        ]);
        $response = $this->post('/login', [
            'email' => 'kasir@test.com',
            'password' => 'salah_banget',
        ]);
        $this->assertGuest();
    }
}