@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0 text-primary"><i class="bi bi-clock-history me-2"></i> Log Aktivitas Sistem (Audit Trail)</h2>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4" style="width: 15%">Waktu</th>
                            <th style="width: 20%">User (Aktor)</th>
                            <th style="width: 15%">Aksi / Target</th>
                            <th style="width: 50%">Detail Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                        <tr>
                            <td class="ps-4 text-nowrap text-white-50">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: bold;">
                                        {{ substr($log->causer->name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $log->causer->name ?? 'System' }}</div>
                                        <small class="text-white-50">{{ $log->causer->roles->first()->name ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($log->event) {
                                        'created' => 'bg-success',
                                        'updated' => 'bg-warning text-dark',
                                        'deleted' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $target = class_basename($log->subject_type);
                                @endphp
                                <span class="badge {{ $badgeColor }} mb-1">{{ strtoupper($log->event) }}</span><br>
                                <small class="text-white-50">{{ $target }} #{{ $log->subject_id }}</small>
                            </td>
                            <td>
                                <div class="mb-1 text-white-50 small">{{ $log->description }}</div>
                                @if($log->properties->has('old') || $log->properties->has('attributes'))
                                    <div class="bg-black p-2 rounded" style="font-family: monospace; font-size: 0.85rem; overflow-x: auto; max-width: 100%;">
                                        @if($log->properties->has('old'))
                                            <div class="text-danger mb-1"><span class="fw-bold">OLD:</span> {{ json_encode($log->properties['old']) }}</div>
                                        @endif
                                        @if($log->properties->has('attributes'))
                                            <div class="text-success"><span class="fw-bold">NEW:</span> {{ json_encode($log->properties['attributes']) }}</div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-white-50">Belum ada log aktivitas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="card-footer text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" border-0 pt-3 pb-3">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection