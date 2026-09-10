<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\KasirService;
use App\Http\Requests\Admin\{StoreKasirRequest, UpdateKasirRequest};

class AdminWaitressController extends Controller
{
    public function index(KasirService $kasirService)
    {
        $kasirs = $kasirService->getPaginatedKasirs();
        return view('admin.waitress.index', compact('kasirs'));
    }

    public function store(StoreKasirRequest $request, KasirService $kasirService)
    {
        $kasirService->createKasir($request->validated());
        return redirect()->route('admin.kasir.index')->with('success', 'Akun staf waitress berhasil dibuat.');
    }

    public function edit($id)
    {
        $kasir = User::findOrFail($id);
        return view('admin.waitress.edit', compact('kasir'));
    }

    public function update(UpdateKasirRequest $request, $id, KasirService $kasirService)
    {
        $kasir = User::findOrFail($id);
        $kasirService->updateKasir($kasir, $request->validated());
        return redirect()->route('admin.kasir.index')->with('success', 'Akun staf waitress diperbarui.');
    }

    public function destroy($id, KasirService $kasirService)
    {
        $kasir = User::findOrFail($id);
        try {
            $kasirService->deleteKasir($kasir);
            return redirect()->route('admin.kasir.index')->with('success', 'Akun staf waitress dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.kasir.index')->with('error', $e->getMessage());
        }
    }
}
