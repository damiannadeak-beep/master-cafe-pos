<?php

namespace Tests\Feature;

use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActiveOrderCardTagBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        Role::create(['name' => 'konsumen', 'guard_name' => 'web']);
        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);
    }

    public function test_active_order_card_and_panes_structure_is_valid()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');
        $meja = Meja::create(['nama_meja_atau_nomor' => '05', 'status' => 'terisi', 'kapasitas' => 4]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'id_meja' => $meja->id,
            'guest_name' => 'Tamu Test',
            'tipe_pesanan' => 'dine_in',
            'status' => 'pending',
            'total' => 20000,
            'tanggal' => now(),
        ]);

        $menu = Menu::create([
            'nama_menu' => 'Kopi Latte',
            'harga' => 20000,
            'kategori' => 'minuman',
            'is_available' => true,
        ]);

        DetailPesanan::create([
            'id_pesanan' => $pesanan->id,
            'id_menu' => $menu->id,
            'jumlah' => 1,
            'subtotal' => 20000,
        ]);

        $response = $this->actingAs($kasir)->get('/kasir/pesanan-aktif');
        $response->assertStatus(200);

        $html = $response->getContent();

        // Verifikasi bahwa tag div seimbang dalam partial active-order-card
        $cardHtml = view('components.waitress.active-order-card', [
            'orders' => Pesanan::where('id', $pesanan->id)->get(),
        ])->render();

        preg_match_all('/<div\b/i', $cardHtml, $opens);
        preg_match_all('/<\/div>/i', $cardHtml, $closes);
        $this->assertSame(count($opens[0]), count($closes[0]), 'Tag <div> dan </div> dalam active-order-card harus seimbang!');

        // Verifikasi bahwa pane-completed-orders dan pane-voided-orders ada di HTML
        $this->assertStringContainsString('id="pane-completed-orders"', $html);
        $this->assertStringContainsString('id="pane-voided-orders"', $html);
        $this->assertStringContainsString('id="pane-active-orders"', $html);

        // Pastikan pane-completed-orders muncul SETELAH penutup pane-active-orders
        $posActivePane = strpos($html, 'id="pane-active-orders"');
        $posCompletedPane = strpos($html, 'id="pane-completed-orders"');
        $posVoidedPane = strpos($html, 'id="pane-voided-orders"');

        $this->assertTrue($posActivePane < $posCompletedPane, 'pane-active-orders harus sebelum pane-completed-orders');
        $this->assertTrue($posCompletedPane < $posVoidedPane, 'pane-completed-orders harus sebelum pane-voided-orders');
    }
}
