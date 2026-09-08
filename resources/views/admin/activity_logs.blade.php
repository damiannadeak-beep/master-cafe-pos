@extends('layouts.admin')

@php
    use App\Helpers\ActivityLogHelper;
@endphp

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 text-white" style="font-family: 'Outfit', sans-serif !important;">
                <i class="bi bi-shield-check me-2" style="color: #c08e5c;"></i> Log Aktivitas Operasional
            </h2>
            <p class="text-secondary small mb-0">Pantau seluruh riwayat transaksi, perubahan stok, dan aktivitas staf kafe secara transparan</p>
        </div>
        
        <!-- Search & Filter Controls -->
        <form action="{{ route('admin.activity_logs.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-2 w-100 w-md-auto">
            <div class="btn-group shadow-sm">
                <a href="{{ request()->fullUrlWithQuery(['event' => null, 'page' => null]) }}" class="btn btn-sm {{ !request('event') ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
                <a href="{{ request()->fullUrlWithQuery(['event' => 'created', 'page' => null]) }}" class="btn btn-sm {{ request('event') == 'created' ? 'btn-success' : 'btn-outline-secondary' }}">Data Baru</a>
                <a href="{{ request()->fullUrlWithQuery(['event' => 'updated', 'page' => null]) }}" class="btn btn-sm {{ request('event') == 'updated' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">Perubahan</a>
                <a href="{{ request()->fullUrlWithQuery(['event' => 'deleted', 'page' => null]) }}" class="btn btn-sm {{ request('event') == 'deleted' ? 'btn-danger' : 'btn-outline-secondary' }}">Penghapusan</a>
            </div>

            <div class="input-group input-group-sm ms-md-2" style="max-width: 260px;">
                <input type="text" name="search" class="form-control" placeholder="Cari aktivitas / staf..." value="{{ request('search') }}" style="background-color: #0e1217; border-color: #21262d; color: white;">
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
                            <th class="ps-4 py-3 text-secondary text-uppercase fw-semibold" style="width: 17%; font-size: 0.78rem; letter-spacing: 0.5px;">Waktu</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="width: 20%; font-size: 0.78rem; letter-spacing: 0.5px;">Aktor / Pelaku</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="width: 20%; font-size: 0.78rem; letter-spacing: 0.5px;">Objek & Aksi</th>
                            <th class="pe-4 py-3 text-secondary text-uppercase fw-semibold" style="width: 43%; font-size: 0.78rem; letter-spacing: 0.5px;">Keterangan & Rincian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                        @php
                            $targetInfo = ActivityLogHelper::formatTarget($log);
                            $eventInfo = ActivityLogHelper::formatEvent($log->event);
                            $friendlyTitle = ActivityLogHelper::getFriendlyDescription($log);

                            $hasOld = $log->properties->has('old');
                            $hasNew = $log->properties->has('attributes');
                            $old = $hasOld ? $log->properties['old'] : [];
                            $new = $hasNew ? $log->properties['attributes'] : [];
                            
                            // Field teknis yang tidak perlu ditampilkan ke pemilik
                            $ignoredKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'total_hpp', 'promo_id', 'id_konsumen', 'id_meja', 'id_kasir'];
                            
                            // Hitung field yang mengalami perubahan nyata
                            $changedFields = [];
                            if ($log->event === 'updated' && $hasNew) {
                                foreach ($new as $k => $v) {
                                    if (in_array($k, $ignoredKeys)) continue;
                                    if (array_key_exists($k, $old) && $old[$k] != $v) {
                                        $changedFields[$k] = [
                                            'old' => $old[$k],
                                            'new' => $v
                                        ];
                                    }
                                }
                            }
                        @endphp
                        <tr style="border-bottom: 1px solid #21262d;">
                            <!-- WAKTU -->
                            <td class="ps-4 text-nowrap align-top py-3">
                                <div class="fw-semibold text-white">{{ $log->created_at->format('d M Y') }}</div>
                                <small class="text-secondary font-monospace">{{ $log->created_at->format('H:i:s') }} WIB</small>
                                <div class="small text-secondary mt-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i>{{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <!-- AKTOR -->
                            <td class="align-top py-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 text-white fw-bold shadow-sm flex-shrink-0" 
                                         style="width: 36px; height: 36px; background: var(--gradient-bronze, #c08e5c); font-size: 0.9rem;">
                                        {{ substr($log->causer->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="max-width: 150px;" title="{{ $log->causer->name ?? 'Sistem Otomatis' }}">
                                            {{ $log->causer->name ?? 'Sistem Otomatis' }}
                                        </div>
                                        <span class="badge" style="background-color: #21262d; color: #8b949e; font-size: 0.72rem;">
                                            {{ ucfirst($log->causer->roles->first()->name ?? 'Sistem') }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- AKSI & TARGET -->
                            <td class="align-top py-3">
                                <span class="badge {{ $eventInfo['badge'] }} mb-2 px-2 py-1 fw-bold" style="font-size: 0.72rem;">
                                    <i class="bi {{ $eventInfo['icon'] }} me-1"></i>{{ $eventInfo['label'] }}
                                </span>
                                <div>
                                    <span class="fw-semibold text-white">
                                        <i class="bi {{ $targetInfo['icon'] }} me-1" style="color: #c08e5c;"></i>{{ $targetInfo['label'] }}
                                    </span>
                                    @if($targetInfo['id'])
                                        <span class="badge ms-1" style="background-color: #0e1217; border: 1px solid #21262d; color: #c08e5c;">{{ $targetInfo['id'] }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- DETAIL KETERANGAN -->
                            <td class="pe-4 align-top py-3">
                                <!-- Judul Ringkasan Ramah Manusia -->
                                <div class="text-white fw-medium mb-2" style="font-size: 0.92rem;">
                                    {{ $friendlyTitle }}
                                </div>

                                <!-- Rincian Perubahan (jika ada) -->
                                @if(count($changedFields) > 0)
                                    <div class="rounded p-2 mb-2 border" style="background-color: #0e1217; border-color: #21262d !important; font-size: 0.82rem;">
                                        @foreach($changedFields as $field => $data)
                                            <div class="d-flex align-items-center flex-wrap gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: rgba(255,255,255,0.06) !important;">
                                                <span class="text-secondary fw-semibold">{{ ActivityLogHelper::formatFieldName($field) }}:</span>
                                                <span class="text-danger small text-decoration-line-through">
                                                    {{ ActivityLogHelper::formatValue($field, $data['old']) }}
                                                </span>
                                                <i class="bi bi-arrow-right text-warning small"></i>
                                                <span class="text-success fw-bold small">
                                                    {{ ActivityLogHelper::formatValue($field, $data['new']) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Opsi Tersembunyi: Data Teknis untuk Debugging Developer -->
                                @if($hasOld || $hasNew)
                                    <div class="mt-1">
                                        <button class="btn btn-sm btn-link text-secondary p-0 text-decoration-none" 
                                                style="font-size: 0.72rem;" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#raw-{{ $log->id }}" 
                                                aria-expanded="false">
                                            <i class="bi bi-code-slash me-1"></i>Lihat Data Teknis (Dev)
                                        </button>
                                        <div class="collapse mt-2" id="raw-{{ $log->id }}">
                                            <div class="rounded p-2 border" style="background-color: #090d12; border-color: #21262d !important;">
                                                <pre class="mb-0 text-white-50" style="font-family: monospace; font-size: 0.72rem; max-height: 120px; overflow-y: auto; white-space: pre-wrap; word-break: break-all;"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-journal-check text-secondary fs-1 d-block mb-2"></i>
                                    <div class="text-white fw-bold">Belum ada aktivitas tercatat</div>
                                    <p class="text-secondary small mb-0">Seluruh transaksi kasir, perubahan stok menu, dan aktivitas staf akan terekam rapi di sini.</p>
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