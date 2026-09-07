<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Menu;
use App\Models\Bahan;
use Spatie\Activitylog\Models\Activity;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);
    }

    public function test_perubahan_harga_menu_tercatat_di_log()
    {
        $pemilik = User::factory()->create();
        $pemilik->assignRole('pemilik');
        $this->actingAs($pemilik);

        $menu = Menu::create([
            'nama_menu' => 'Nasi Goreng',
            'harga' => 15000,
            'stok' => 20,
            'kategori' => 'makanan',
            'is_available' => true,
        ]);

        // Pastikan event created tercatat
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Menu::class,
            'event' => 'created',
            'causer_id' => $pemilik->id,
        ]);

        // Simulasikan update harga
        $menu->update(['harga' => 20000]);

        // Pastikan event updated tercatat
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Menu::class,
            'event' => 'updated',
        ]);

        // Cek detail properties JSON di activity log
        $log = Activity::where('subject_type', Menu::class)->where('event', 'updated')->first();
        $this->assertNotNull($log);
        $this->assertEquals(15000, $log->properties['old']['harga']);
        $this->assertEquals(20000, $log->properties['attributes']['harga']);
    }
}