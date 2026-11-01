@extends('layouts.app')

@section('title', 'Shipment ' . $shipment->shipment_number)
@section('page_heading', 'Detail Pengiriman & Timeline Pelacakan')

@section('content')
<div class="container-fluid px-0">
    @include('components.alert')

    <!-- Top Action & Navigation Bar -->
    <div class="card mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="fw-bold mb-0 font-monospace">{{ $shipment->shipment_number }}</h4>
                    @php
                        $statusClasses = [
                            'CREATED' => 'badge-gradient-neutral',
                            'BOOKED' => 'badge-gradient-info',
                            'PICKED_UP' => 'badge-gradient-info',
                            'IN_TRANSIT' => 'badge-gradient-accent',
                            'DELIVERED' => 'badge-gradient-success',
                            'CANCELLED' => 'badge-gradient-danger',
                        ];
                    @endphp
                    <span class="badge {{ $statusClasses[$shipment->current_status] ?? 'badge-gradient-neutral' }} fs-6">
                        {{ $shipment->current_status }}
                    </span>
                    @if($shipment->is_delayed)
                        <span class="badge bg-danger text-white">
                            <i class="bi bi-clock-history me-1"></i> DELAYED (+{{ $shipment->delay_duration_hours }} Jam)
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-check-circle me-1"></i> ON-TIME
                        </span>
                    @endif
                </div>
                <p class="text-muted small mb-0">
                    Didaftarkan pada {{ $shipment->created_at->format('d M Y H:i') }} &bull; Customer: <strong>{{ $shipment->customer?->name ?? 'N/A' }}</strong>
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('shipments.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>

                @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appendCheckpointModal">
                        <i class="bi bi-geo-alt-fill me-1"></i> Catat Checkpoint
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#transitionStatusModal">
                        <i class="bi bi-arrow-left-right me-1"></i> Ubah Status
                    </button>
                    <a href="{{ route('shipments.edit', $shipment) }}" class="btn btn-outline-secondary" title="Edit Metadata">
                        <i class="bi bi-pencil"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Delay Alert Banner if present -->
    @if($shipment->is_delayed)
        <div class="alert alert-danger d-flex align-items-start gap-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-3 text-danger mt-1"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Peringatan Keterlambatan Rute (Delay Detected)</h5>
                <p class="mb-1">
                    Pengiriman ini mengalami deviasi jadwal sebesar <strong>{{ $shipment->delay_duration_hours }} jam</strong> dengan tingkat keparahan <strong>{{ $shipment->delay_severity ?? 'MEDIUM' }}</strong>.
                </p>
                @if($shipment->delay_reason)
                    <small class="d-block text-muted"><strong>Penyebab:</strong> {{ $shipment->delay_reason }}</small>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Route, Manifest & Timeline -->
        <div class="col-12 col-lg-8">
            <!-- Route Card -->
            <div class="card mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-5 text-center text-md-start">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Titik Asal (Origin)</small>
                        <h5 class="fw-bold mb-0 font-monospace">{{ $shipment->originLocation?->code }}</h5>
                        <div class="fw-semibold">{{ $shipment->originLocation?->name }}</div>
                        <small class="text-muted">{{ $shipment->originLocation?->city }}, {{ $shipment->originLocation?->country }}</small>
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> Keberangkatan: <strong>{{ $shipment->expected_departure->format('d M Y H:i') }}</strong>
                        </div>
                    </div>

                    <div class="col-12 col-md-2 text-center my-2 my-md-0">
                        <div class="d-inline-flex flex-column align-items-center">
                            <span class="badge bg-light text-dark border px-2 py-1 mb-1 font-monospace">{{ $shipment->carrier?->code }}</span>
                            <i class="bi bi-arrow-right fs-3 text-muted d-none d-md-block"></i>
                            <i class="bi bi-arrow-down fs-3 text-muted d-md-none"></i>
                            <small class="text-muted fw-bold" style="font-size: 0.7rem;">{{ $shipment->carrier?->transport_mode }}</small>
                        </div>
                    </div>

                    <div class="col-12 col-md-5 text-center text-md-end">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Titik Tujuan (Destination)</small>
                        <h5 class="fw-bold mb-0 font-monospace">{{ $shipment->destinationLocation?->code }}</h5>
                        <div class="fw-semibold">{{ $shipment->destinationLocation?->name }}</div>
                        <small class="text-muted">{{ $shipment->destinationLocation?->city }}, {{ $shipment->destinationLocation?->country }}</small>
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-calendar-check me-1"></i> Estimasi Tiba: 
                            <strong>
                                {{ $shipment->actual_arrival ? $shipment->actual_arrival->format('d M Y H:i') : ($shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y H:i') : $shipment->expected_arrival->format('d M Y H:i')) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chronological Tracking Timeline Engine -->
            <div class="card mb-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                    <div>
                        <h5 class="fw-bold mb-0">Riwayat Perjalanan (Tracking Timeline)</h5>
                        <small class="text-muted">Append-only audit trail pencatatan checkpoint secara kronologis.</small>
                    </div>
                    <span class="badge badge-gradient-accent">
                        {{ $shipment->trackingEvents->count() }} Checkpoints
                    </span>
                </div>

                <div class="tracking-timeline position-relative ps-4 ms-2">
                    <!-- Vertical Timeline Line -->
                    <div class="timeline-line position-absolute top-0 bottom-0 start-0 border-start border-2 border-secondary-subtle" style="left: 12px !important;"></div>

                    @forelse($shipment->trackingEvents as $event)
                        <div class="timeline-item position-relative mb-4 ps-3">
                            <!-- Bullet Marker -->
                            @php
                                $markerColor = match($event->event_type) {
                                    'EXCEPTION' => 'bg-danger text-white border-danger',
                                    'SYSTEM' => 'bg-secondary text-white border-secondary',
                                    default => 'bg-dark text-white border-dark',
                                };
                            @endphp
                            <div class="timeline-marker position-absolute rounded-circle border border-2 d-flex align-items-center justify-content-center {{ $markerColor }}" 
                                 style="left: -20px; top: 2px; width: 24px; height: 24px; font-size: 0.7rem; z-index: 2;">
                                @if($event->event_type === 'EXCEPTION')
                                    <i class="bi bi-exclamation"></i>
                                @elseif($event->status === 'DELIVERED')
                                    <i class="bi bi-check-lg"></i>
                                @else
                                    <i class="bi bi-dot"></i>
                                @endif
                            </div>

                            <div class="card p-3 shadow-none border bg-white">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ $statusClasses[$event->status] ?? 'badge-gradient-neutral' }}">
                                            {{ $event->status }}
                                        </span>
                                        @if($event->event_type !== 'NORMAL')
                                            <span class="badge bg-warning-subtle text-dark border">
                                                {{ $event->event_type }}
                                            </span>
                                        @endif
                                        <span class="fw-semibold text-dark">
                                            {{ $event->location ? $event->location->name . ' (' . $event->location->code . ')' : 'Open Sea / In Transit Coordinates' }}
                                        </span>
                                    </div>
                                    <small class="text-muted font-monospace">
                                        <i class="bi bi-clock me-1"></i>{{ $event->occurred_at->format('d M Y H:i:s') }}
                                    </small>
                                </div>

                                <p class="mb-1 text-secondary small">
                                    {{ $event->description }}
                                </p>

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 pt-2 border-top small text-muted">
                                    <div>
                                        @if($event->latitude && $event->longitude)
                                            <span class="font-monospace">
                                                <i class="bi bi-geo-alt me-1"></i>GPS: {{ $event->latitude }}, {{ $event->longitude }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <span>Dicatat oleh: <strong>{{ $event->creator?->name ?? 'System' }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-geo fs-2 d-block mb-2 opacity-50"></i>
                            Belum ada checkpoint tracking yang dicatat.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Manifest Cargo Items Table -->
            <div class="card mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Manifest Muatan Kargo (Line Items)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SKU</th>
                                <th>Deskripsi Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Berat Satuan</th>
                                <th class="text-end">Volume Satuan</th>
                                <th class="text-end">Declared Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipment->items as $item)
                                <tr>
                                    <td class="font-monospace fw-semibold">{{ $item->sku ?? '-' }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center fw-bold">{{ number_format($item->quantity) }}</td>
                                    <td class="text-end">{{ number_format($item->weight_kg, 2) }} kg</td>
                                    <td class="text-end">{{ number_format($item->volume_cbm, 2) }} CBM</td>
                                    <td class="text-end">${{ number_format($item->declared_value, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">Tidak ada rincian item manifest.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Shipment Meta & Quick Specs -->
        <div class="col-12 col-lg-4">
            <div class="card mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Spesifikasi Pengiriman</h5>

                <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Berat Kargo:</span>
                        <strong class="font-monospace fs-6">{{ number_format($shipment->total_weight_kg, 2) }} kg</strong>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Volume Kargo:</span>
                        <strong class="font-monospace fs-6">{{ number_format($shipment->total_volume_cbm, 2) }} CBM</strong>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Klasifikasi Kargo:</span>
                        <span class="badge badge-gradient-info">{{ $shipment->cargo_type }}</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">No. Referensi / PO:</span>
                        <span class="font-monospace">{{ $shipment->reference_number ?? '-' }}</span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Koordinat Terakhir:</span>
                        <span class="font-monospace">
                            {{ $shipment->current_lat && $shipment->current_lng ? $shipment->current_lat . ', ' . $shipment->current_lng : 'Lokasi Asal' }}
                        </span>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Dibuat Oleh:</span>
                        <span>{{ $shipment->creator?->name ?? 'System' }}</span>
                    </li>
                </ul>

                @if($shipment->notes)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted d-block fw-bold mb-1">Catatan Khusus:</small>
                        <div class="p-2 bg-light rounded text-secondary small" style="white-space: pre-wrap;">{{ $shipment->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Append Checkpoint Tracking Event -->
@if(Auth::user()->isAdmin() || Auth::user()->isOperator())
    <div class="modal fade" id="appendCheckpointModal" tabindex="-1" aria-labelledby="appendCheckpointModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('shipments.tracking.store', $shipment) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="appendCheckpointModalLabel">Catat Checkpoint Tracking Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi Checkpoint</label>
                            <select name="location_id" id="checkpointLocationSelect" class="form-select">
                                <option value="">Titik Lepas Pantai / Koordinat Langsung</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" data-lat="{{ $loc->latitude }}" data-lng="{{ $loc->longitude }}">
                                        {{ $loc->code }} &bull; {{ $loc->name }} ({{ $loc->country }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Status Pengiriman <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED'] as $st)
                                        <option value="{{ $st }}" {{ $shipment->current_status === $st ? 'selected' : '' }}>
                                            {{ $st }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Tipe Event <span class="text-danger">*</span></label>
                                <select name="event_type" class="form-select" required>
                                    <option value="NORMAL" selected>NORMAL</option>
                                    <option value="EXCEPTION">EXCEPTION</option>
                                    <option value="SYSTEM">SYSTEM</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Latitude</label>
                                <input type="number" step="0.0000001" name="latitude" id="checkpointLat" class="form-control" placeholder="Contoh: 1.290270">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Longitude</label>
                                <input type="number" step="0.0000001" name="longitude" id="checkpointLng" class="form-control" placeholder="Contoh: 103.851959">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Waktu Kejadian (Timestamp) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="occurred_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            <small class="text-muted" style="font-size: 0.72rem;">Waktu tidak boleh lebih lampau dari checkpoint terakhir.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi Aktivitas Checkpoint <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Contoh: Container loaded onto vessel MV EVER GIVEN. Customs inspection cleared." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-1"></i> Simpan Checkpoint
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 2: Direct State Machine Transition -->
    <div class="modal fade" id="transitionStatusModal" tabindex="-1" aria-labelledby="transitionStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('shipments.transition', $shipment) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="transitionStatusModalLabel">Transisi Status Pengiriman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Status saat ini: <strong>{{ $shipment->current_status }}</strong>. Sistem CargoTrace memberlakukan validasi state machine yang ketat.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status Baru <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED'] as $target)
                                    @if($target !== $shipment->current_status)
                                        <option value="{{ $target }}">{{ $target }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alasan Perubahan Status</label>
                            <textarea name="reason" class="form-control" rows="2" placeholder="Catatan log audit transisi status..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-arrow-repeat me-1"></i> Perbarui Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const locSelect = document.getElementById('checkpointLocationSelect');
        const latInput = document.getElementById('checkpointLat');
        const lngInput = document.getElementById('checkpointLng');

        if (locSelect) {
            locSelect.addEventListener('change', function () {
                const opt = locSelect.options[locSelect.selectedIndex];
                if (opt && opt.dataset.lat && opt.dataset.lng) {
                    latInput.value = opt.dataset.lat;
                    lngInput.value = opt.dataset.lng;
                }
            });
        }
    });
    </script>
@endif
@endsection
