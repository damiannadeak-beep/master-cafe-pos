<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Rating;
use App\Models\Pesanan;
use App\Models\Promo;
use App\Models\KasirShift;

class AdminReportsAndFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'konsumen', 'guard_name' => 'web']);
        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);
    }

    /** Test 1: Admin ekspor laporan penjualan ke CSV */
    public function test_admin_dapat_ekspor_laporan_csv()
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        $response = $this->actingAs($admin)->get('/admin/reports/csv');

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type'), 'vnd.ms-excel') ||
            str_contains($response->headers->get('content-type'), 'text/csv')
        );
    }

    /** Test 2: Admin membalas ulasan konsumen */
    public function test_admin_dapat_membalas_ulasan_konsumen()
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        $konsumen = User::factory()->create();
        $konsumen->assignRole('konsumen');

        $pesanan = Pesanan::create([
            'id_konsumen' => $konsumen->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'completed',
            'tanggal' => now(),
            'total' => 20000,
        ]);

        $rating = Rating::create([
            'id_pesanan' => $pesanan->id,
            'id_konsumen' => $konsumen->id,
            'rating' => 4,
            'komentar' => 'Rasa enak tapi sambalnya kurang pedas',
            'tanggal' => now(),
        ]);

        $response = $this->actingAs($admin)->post("/admin/reviews/{$rating->id}/reply", [
            'balasan_admin' => 'Terima kasih masukannya, next order bisa request extra sambal ya!',
        ]);

        $response->assertRedirect('/admin/reviews');
        $this->assertEquals('Terima kasih masukannya, next order bisa request extra sambal ya!', $rating->fresh()->balasan_admin);
    }

    /** Test 3: Kasir ekspor laporan shift ke Excel */
    public function test_kasir_dapat_ekspor_laporan_shift_excel()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $shift = KasirShift::create([
            'user_id' => $kasir->id,
            'modal_awal' => 50000,
            'status' => 'open',
            'waktu_buka' => now(),
        ]);

        $response = $this->actingAs($kasir)->get('/kasir/shift-report/excel');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'vnd.ms-excel'));
    }

    /** Test 4: Admin membuat voucher promo diskon baru */
    public function test_admin_dapat_membuat_promo()
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        $response = $this->actingAs($admin)->post('/admin/promo', [
            'title' => 'Diskon Awal Bulan 10%',
            'type' => 'discount',
            'discount_type' => 'percentage',
            'value' => 10,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addDays(7)->toDateString(),
            'is_active' => 'on',
        ]);

        $response->assertRedirect('/admin/promo');
        $this->assertDatabaseHas('promos', [
            'title' => 'Diskon Awal Bulan 10%',
            'value' => 10,
        ]);
    }
}
