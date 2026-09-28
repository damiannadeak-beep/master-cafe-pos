<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WaitressStokToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_waitress_can_toggle_menu_availability(): void
    {
        \Spatie\Permission\Models\Role::create(['name' => 'kasir']);
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $menu = Menu::create([
            'nama_menu' => 'Kopi Susu Gula Aren',
            'harga' => 18000,
            'stok' => 20,
            'kategori' => 'minuman',
            'is_available' => true,
        ]);

        // 1. Explicit set to false (Habis)
        $res1 = $this->actingAs($kasir)->putJson("/kasir/stok/{$menu->id}/toggle", [
            'is_available' => false,
        ]);
        $res1->assertStatus(200);
        $res1->assertJson([
            'success' => true,
            'is_available' => false,
        ]);
        $this->assertDatabaseHas('menu', [
            'id' => $menu->id,
            'is_available' => false,
        ]);

        // 2. Calling set to false again must NOT toggle back to true (idempotent)
        $res2 = $this->actingAs($kasir)->putJson("/kasir/stok/{$menu->id}/toggle", [
            'is_available' => false,
        ]);
        $res2->assertStatus(200);
        $res2->assertJson([
            'success' => true,
            'is_available' => false,
        ]);
        $this->assertDatabaseHas('menu', [
            'id' => $menu->id,
            'is_available' => false,
        ]);

        // 3. Explicit set to true (Tersedia)
        $res3 = $this->actingAs($kasir)->putJson("/kasir/stok/{$menu->id}/toggle", [
            'is_available' => true,
        ]);
        $res3->assertStatus(200);
        $res3->assertJson([
            'success' => true,
            'is_available' => true,
        ]);
        $this->assertDatabaseHas('menu', [
            'id' => $menu->id,
            'is_available' => true,
        ]);
    }
}
