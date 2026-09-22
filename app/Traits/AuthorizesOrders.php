<?php

namespace App\Traits;

use App\Models\Pesanan;
use Illuminate\Http\Request;

/**
 * Trait AuthorizesOrders
 *
 * Menyediakan logika otorisasi kepemilikan pesanan yang konsisten
 * untuk digunakan di berbagai controller (Payment, Order, dll).
 */
trait AuthorizesOrders
{
    /**
     * Verifikasi apakah request saat ini berhak mengakses pesanan.
     *
     * Pengecekan dilakukan dalam urutan:
     * 1. User login yang merupakan pemilik pesanan (id_konsumen)
     * 2. Token order_token yang cocok (guest via QR)
     * 3. Session active_order_id yang cocok (guest tanpa token)
     * 4. Staff (admin/kasir/pemilik/waitress) yang mengakses pesanan meja mereka
     *
     * @param  Pesanan  $pesanan
     * @param  Request  $request
     * @return bool
     */
    protected function isOrderOwner(Pesanan $pesanan, Request $request): bool
    {
        $token = $request->input('token')
            ?? $request->query('token')
            ?? $request->header('X-Order-Token')
            ?? session('order_token');

        // 1. User login yang merupakan pemilik pesanan
        if (auth()->check() && $pesanan->id_konsumen && $pesanan->id_konsumen == auth()->id()) {
            return true;
        }

        // 2. Token order_token yang cocok (guest via QR/link)
        if ($pesanan->order_token && $token && hash_equals((string)$pesanan->order_token, (string)$token)) {
            return true;
        }

        // 3. Session active_order_id yang cocok (guest tanpa token legacy)
        if (!$pesanan->id_konsumen && empty($pesanan->order_token) && session('active_order_id') == $pesanan->id) {
            return true;
        }

        return false;
    }

    /**
     * Verifikasi apakah request berhak mengakses pesanan (termasuk staff).
     *
     * Sama seperti isOrderOwner() tetapi juga mengizinkan staff kafe
     * (admin, kasir, pemilik, waitress) untuk mengakses pesanan di meja mereka.
     *
     * @param  Pesanan  $pesanan
     * @param  Request  $request
     * @return bool
     */
    protected function isOrderAuthorized(Pesanan $pesanan, Request $request): bool
    {
        if ($this->isOrderOwner($pesanan, $request)) {
            return true;
        }

        // Staff kafe boleh mengakses pesanan meja
        if ($pesanan->id_meja && (
            session('id_meja') == $pesanan->id_meja ||
            (auth()->check() && in_array(auth()->user()->role ?? '', ['admin', 'kasir', 'pemilik', 'waitress']))
        )) {
            return true;
        }

        return false;
    }

    /**
     * Abort 403 jika tidak berhak mengakses pesanan.
     *
     * @param  Pesanan  $pesanan
     * @param  Request  $request
     * @return void
     */
    protected function authorizeOrderAccess(Pesanan $pesanan, Request $request): void
    {
        if (!$this->isOrderAuthorized($pesanan, $request)) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }
    }

    /**
     * Abort 403 jika bukan pemilik pesanan (lebih ketat, tanpa staff access).
     *
     * @param  Pesanan  $pesanan
     * @param  Request  $request
     * @return void
     */
    protected function authorizeOrderOwner(Pesanan $pesanan, Request $request): void
    {
        if (!$this->isOrderOwner($pesanan, $request)) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }
    }
}
