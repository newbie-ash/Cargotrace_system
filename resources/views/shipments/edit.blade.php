@extends('layouts.app')

@section('title', 'Edit Shipment ' . $shipment->shipment_number)
@section('page_heading', 'Perbarui Informasi Pengiriman')

@section('content')
<div class="container-fluid px-0">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1 font-monospace">Edit Metadata: {{ $shipment->shipment_number }}</h4>
                <p class="text-muted small mb-0">
                    Perbarui parameter carrier, jadwal kedatangan yang diharapkan, dan catatan operasional.
                </p>
            </div>
            <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Detail</span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                <form action="{{ route('shipments.update', $shipment) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Carrier / Pelayaran <span class="text-danger">*</span></label>
                            <select name="carrier_id" class="form-select @error('carrier_id') is-invalid @enderror" required>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ old('carrier_id', $shipment->carrier_id) == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->name }} ({{ $carrier->code }} &bull; {{ $carrier->transport_mode }})
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">No. Referensi Customer / PO</label>
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number', $shipment->reference_number) }}">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Estimasi Kedatangan (ETA) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="expected_arrival" class="form-control @error('expected_arrival') is-invalid @enderror" value="{{ old('expected_arrival', $shipment->expected_arrival ? $shipment->expected_arrival->format('Y-m-d\TH:i') : '') }}" required>
                            @error('expected_arrival')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan Operasional / Instruksi Khusus</label>
                            <textarea name="notes" class="form-control" rows="4">{{ old('notes', $shipment->notes) }}</textarea>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check2 me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card bg-light">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Atribut Terproteksi (Immutable)</h5>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Nomor Shipment:</span>
                        <strong class="font-monospace">{{ $shipment->shipment_number }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Customer Akun:</span>
                        <span>{{ $shipment->customer?->name }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Lokasi Asal (Origin):</span>
                        <span>{{ $shipment->originLocation?->code }} - {{ $shipment->originLocation?->city }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Lokasi Tujuan (Dest):</span>
                        <span>{{ $shipment->destinationLocation?->code }} - {{ $shipment->destinationLocation?->city }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Status Saat Ini:</span>
                        <span class="badge badge-gradient-accent">{{ $shipment->current_status }}</span>
                    </li>
                </ul>
                <div class="alert alert-warning small mt-3 mb-0 py-2">
                    <i class="bi bi-shield-lock me-1"></i>
                    Rute asal, tujuan, dan customer tidak dapat diubah setelah shipment didaftarkan untuk menjaga integritas data logistik.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
