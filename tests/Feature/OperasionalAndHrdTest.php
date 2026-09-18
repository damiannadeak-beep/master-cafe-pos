<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Setting;
use App\Models\Pengeluaran;
use App\Models\PengeluaranBisnis;
use App\Models\Absensi;

class OperasionalAndHrdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);
    }

    /** Test 1: Kasir mencatat pengeluaran kas harian */
    public function test_kasir_dapat_mencatat_pengeluaran()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $response = $this->actingAs($kasir)->post('/kasir/pengeluaran', [
            'deskripsi' => 'Beli Es Batu Kristal 2 Karung',
            'nominal' => 24000,
            'keterangan' => 'Stok es batu habis di jam sibuk',
        ]);

        $response->assertRedirect('/kasir/pengeluaran');
        $this->assertDatabaseHas('pengeluarans', [
            'user_id' => $kasir->id,
            'deskripsi' => 'Beli Es Batu Kristal 2 Karung',
            'nominal' => 24000,
        ]);
    }

    /** Test 2: Admin dapat mencatat pengeluaran bisnis / usaha */
    public function test_admin_dapat_mencatat_pengeluaran_bisnis()
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        $response = $this->actingAs($admin)->post('/admin/pengeluaran-bisnis', [
            'tanggal' => date('Y-m-d'),
            'kategori' => 'stok_bahan',
            'deskripsi' => 'Beli Biji Kopi Arabika 5kg',
            'nominal' => 350000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengeluaran_bisnis', [
            'deskripsi' => 'Beli Biji Kopi Arabika 5kg',
            'nominal' => 350000,
        ]);
    }

    /** Test 4: Kasir melakukan absensi clock-in dengan koordinat GPS */
    public function test_kasir_dapat_melakukan_absensi_clock_in()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        // Atur koordinat warung
        Setting::updateOrCreate(['key' => 'warung_latitude'], ['value' => '-6.200000']);
        Setting::updateOrCreate(['key' => 'warung_longitude'], ['value' => '106.816666']);
        Setting::updateOrCreate(['key' => 'absensi_radius_meter'], ['value' => '100']);

        $response = $this->actingAs($kasir)->post('/kasir/absensi', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('absensis', [
            'user_id' => $kasir->id,
        ]);
        $this->assertContains(Absensi::where('user_id', $kasir->id)->first()->status, ['hadir', 'terlambat']);
    }

    /** Test 5: Admin dapat melihat laporan rekap absensi karyawan */
    public function test_admin_dapat_melihat_rekap_absensi()
    {
        $admin = User::factory()->create();
        $admin->assignRole('pemilik');

        $response = $this->actingAs($admin)->get('/admin/absensi');
        $response->assertStatus(200);
    }
}
