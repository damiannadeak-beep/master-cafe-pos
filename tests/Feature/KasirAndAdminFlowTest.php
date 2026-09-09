<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Menu;
use App\Models\Bahan;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use App\Models\KasirShift;

class KasirAndAdminFlowTest extends TestCase
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

    /** Test 1: Buka Shift Kasir */
    public function test_kasir_dapat_membuka_shift()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $response = $this->actingAs($kasir)->post('/kasir/shift/buka', [
            'modal_awal' => 100000,
        ]);

        $response->assertRedirect('/kasir/pos');
        $this->assertDatabaseHas('kasir_shifts', [
            'user_id' => $kasir->id,
            'modal_awal' => 100000,
            'status' => 'open',
        ]);
    }

    /** Test 2: Tutup Shift Kasir dengan Rekonsiliasi Kas */
    public function test_kasir_dapat_menutup_shift()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'modal_awal' => 100000,
            'status' => 'open',
            'waktu_buka' => now(),
        ]);

        $response = $this->actingAs($kasir)->post('/kasir/shift/tutup', [
            'uang_fisik_aktual' => 150000,
            'catatan' => 'Shift siang lancar',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('kasir_shifts', [
            'id' => $shift->id,
            'status' => 'closed',
            'uang_fisik_aktual' => 150000,
        ]);
    }

    /** Test 3: Kasir Membayar Pesanan (Pay Order) */
    public function test_kasir_dapat_memproses_pembayaran_pesanan()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'pending',
            'tanggal' => now(),
            'total' => 20000,
        ]);

        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'status' => 'unpaid',
            'total_bayar' => 20000,
        ]);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/pay", [
            'metode' => 'cash',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('paid', $pesanan->fresh()->pembayaran->status);
    }

    /** Test 4: Kasir Update Status Pesanan (Processing -> Completed) */
    public function test_kasir_dapat_update_status_pesanan()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'dine_in',
            'status' => 'pending',
            'tanggal' => now(),
            'total' => 30000,
        ]);

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('completed', $pesanan->fresh()->status);
    }

    /** Test 5: Kasir Cetak Struk Pesanan yang Lunas */
    public function test_kasir_dapat_melihat_receipt_pesanan_lunas()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'completed',
            'tanggal' => now(),
            'total' => 20000,
        ]);

        Pembayaran::create([
            'id_pesanan' => $pesanan->id,
            'status' => 'paid',
            'metode' => 'cash',
            'total_bayar' => 20000,
        ]);

        $response = $this->actingAs($kasir)->get("/kasir/order/{$pesanan->id}/receipt");
        $response->assertStatus(200);
        $response->assertSee('MASTER CAFE POS');
    }

    /** Test 6: Admin Backoffice Mengakses Dashboard */
    public function test_admin_dapat_mengakses_dashboard_dan_kelola_menu()
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        // Akses kelola menu
        $responseMenu = $this->actingAs($admin)->get('/admin/menu');
        $responseMenu->assertStatus(200);
    }
}
