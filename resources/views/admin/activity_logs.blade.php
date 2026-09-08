@extends('layouts.admin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 text-white" style="font-family: 'Outfit', sans-serif !important;">
                <i class="bi bi-clock-history me-2" style="color: #c08e5c;"></i> Log Aktivitas Sistem (Audit Trail)
            </h2>
            <p class="text-secondary small mb-0">Riwayat audit trail pencatatan aktivitas pengguna dan perubahan data sistem</p>
        </div>
        
        <!-- Search & Filter Controls -->
        <form action="{{ route('admin.activity_logs.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-2 w-100 w-md-auto">
            <div class="btn-group shadow-sm">
                <a href="{{ request()->fullUrlWithQuery(['event' => null, 'page' => null]) }}" class="btn btn-sm {{ !request('event') ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
                <a href="{{ request()->fullUrlWithQuery(['event' => 'created', 'page' => null]) }}" class="btn btn-sm {{ request('event') == 'created' ? 'btn-success' : 'btn-outline-secondary' }}">Created</a>
                <a href="{{ request()->fullUrlWithQuery(['event' => 'updated', 'page' => null]) }}" class="btn btn-sm {{ request('event') == 'updated' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">Updated</a>
                <a href="{{ request()->fullUrlWithQuery(['event' => 'deleted', 'page' => null]) }}" class="btn btn-sm {{ request('event') == 'deleted' ? 'btn-danger' : 'btn-outline-secondary' }}">Deleted</a>
            </div>

            <div class="input-group input-group-sm ms-md-2" style="max-width: 260px;">
                <input type="text" name="search" class="form-control" placeholder="Cari deskripsi / target..." value="{{ request('search') }}" style="background-color: #0e1217; border-color: #21262d; color: white;">
                @if(request('event'))
                    <input type="hidden" name="event" value="{{ request('event') }}">
                @endif
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                @if(request('search') || request('event'))
                    <a href="{{ route('admin.activity_logs.index') }}" class="btn btn-outline-danger" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="card shadow-sm rounded-4 overflow-hidden border-0" style="background-color: #161b22; border: 1px solid #21262d !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: #e6edf3; border-color: #21262d;">
                    <thead style="background-color: #0e1217; border-bottom: 2px solid #21262d;">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fw-semibold" style="width: 16%; font-size: 0.78rem; letter-spacing: 0.5px;">Waktu</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="width: 18%; font-size: 0.78rem; letter-spacing: 0.5px;">User (Aktor)</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="width: 16%; font-size: 0.78rem; letter-spacing: 0.5px;">Aksi & Target</th>
                            <th class="pe-4 py-3 text-secondary text-uppercase fw-semibold" style="width: 50%; font-size: 0.78rem; letter-spacing: 0.5px;">Detail Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                        <tr style="border-bottom: 1px solid #21262d;">
                            <!-- WAKTU -->
                            <td class="ps-4 text-nowrap align-top py-3">
                                <div class="fw-semibold text-white">{{ $log->created_at->format('d M Y') }}</div>
                                <small class="text-secondary font-monospace">{{ $log->created_at->format('H:i:s') }}</small>
                                <div class="small text-secondary" style="font-size: 0.75rem;">{{ $log->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- AKTOR -->
                            <td class="align-top py-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 text-white fw-bold shadow-sm flex-shrink-0" 
                                         style="width: 36px; height: 36px; background: var(--gradient-bronze, #c08e5c); font-size: 0.9rem;">
                                        {{ substr($log->causer->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="max-width: 140px;" title="{{ $log->causer->name ?? 'System' }}">
                                            {{ $log->causer->name ?? 'System' }}
                                        </div>
                                        <span class="badge" style="background-color: #21262d; color: #8b949e; font-size: 0.72rem;">
                                            {{ $log->causer->roles->first()->name ?? 'System' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- AKSI & TARGET -->
                            <td class="align-top py-3">
                                @php
                                    $badgeClass = match($log->event) {
                                        'created' => 'bg-success text-white',
                                        'updated' => 'bg-warning text-dark',
                                        'deleted' => 'bg-danger text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                    $target = class_basename($log->subject_type);
                                @endphp
                                <span class="badge {{ $badgeClass }} mb-1 px-2 py-1 text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                    {{ $log->event }}
                                </span>
                                <div class="text-secondary small font-monospace mt-1">
                                    {{ $target ?: 'Sistem' }}
                                    @if($log->subject_id)
                                        <span class="text-white-50">#{{ $log->subject_id }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- DETAIL PERUBAHAN -->
                            <td class="pe-4 align-top py-3">
                                <div class="mb-2 text-light small fw-medium">{{ $log->description }}</div>
                                
                                @php
                                    $hasOld = $log->properties->has('old');
                                    $hasNew = $log->properties->has('attributes');
                                    $old = $hasOld ? $log->properties['old'] : [];
                                    $new = $hasNew ? $log->properties['attributes'] : [];
                                    
                                    // Hitung field yang benar-benar mengalami perubahan
                                    $changedKeys = [];
                                    if ($log->event === 'updated' && $hasNew) {
                                        foreach ($new as $k => $v) {
                                            if ($k === 'updated_at') continue;
                                            if (array_key_exists($k, $old) && $old[$k] != $v) {
                                                $changedKeys[] = $k;
                                            }
                                        }
                                    }
                                @endphp

                                @if($log->event === 'updated' && count($changedKeys) > 0)
                                    <!-- Visual Diff untuk data Updated -->
                                    <div class="rounded p-2 border" style="background-color: #090d12; border-color: #21262d !important; font-size: 0.8rem;">
                                        @foreach($changedKeys as $key)
                                            <div class="d-flex align-items-center flex-wrap gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: rgba(255,255,255,0.06) !important;">
                                                <span class="badge text-secondary font-monospace" style="background-color: #161b22; border: 1px solid #21262d;">{{ $key }}</span>
                                                <span class="text-danger small font-monospace text-decoration-line-through">
                                                    {{ is_array($old[$key]) ? json_encode($old[$key]) : ($old[$key] === null ? 'null' : (string)$old[$key]) }}
                                                </span>
                                                <i class="bi bi-arrow-right text-warning small"></i>
                                                <span class="text-success small font-monospace fw-bold">
                                                    {{ is_array($new[$key]) ? json_encode($new[$key]) : ($new[$key] === null ? 'null' : (string)$new[$key]) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($hasOld || $hasNew)
                                    <!-- Tampilan Attributes / Old Data yang rapi -->
                                    <div class="rounded p-2 border" style="background-color: #090d12; border-color: #21262d !important;">
                                        <pre class="mb-0 text-white-50" style="font-family: monospace; font-size: 0.76rem; max-height: 140px; overflow-y: auto; white-space: pre-wrap; word-break: break-all;"><code>{{ json_encode($new ?: $old, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                    </div>
                                @else
                                    <span class="text-secondary small fst-italic">Tidak ada metadata tambahan.</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-journal-x text-secondary fs-1 d-block mb-2"></i>
                                    <div class="text-white fw-bold">Belum ada log aktivitas</div>
                                    <p class="text-secondary small mb-0">Aktivitas sistem dan perubahan data akan tercatat otomatis di sini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination -->
            @if($logs->hasPages())
            <div class="card-footer px-4 py-3 border-top" 
                 style="background-color: #0e1217; border-color: #21262d !important;">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection