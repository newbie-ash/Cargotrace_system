@extends('layouts.app')

@section('title', 'Buat Shipment Baru')
@section('page_heading', 'Registrasi Pengiriman Kargo')

@section('content')
<div class="container-fluid px-0">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Registrasi Order Pengiriman Baru</h4>
                <p class="text-muted small mb-0">
                    Input rute asal, tujuan, moda carrier, jadwal keberangkatan, dan manifest item kargo.
                </p>
            </div>
            <a href="{{ route('shipments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <form action="{{ route('shipments.store') }}" method="POST" id="shipmentForm">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Core Logistics Routing -->
            <div class="col-12 col-lg-7">
                <div class="card mb-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">1. Parameter Rute & Mitra Logistik</h5>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Customer Akun <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Pilih Customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->company_name ?? $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Carrier / Pelayaran <span class="text-danger">*</span></label>
                            <select name="carrier_id" class="form-select @error('carrier_id') is-invalid @enderror" required>
                                <option value="">Pilih Carrier...</option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->name }} ({{ $carrier->code }} &bull; {{ $carrier->transport_mode }})
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pelabuhan / Hub Asal (Origin) <span class="text-danger">*</span></label>
                            <select name="origin_location_id" id="originLocation" class="form-select @error('origin_location_id') is-invalid @enderror" required>
                                <option value="">Pilih Lokasi Asal...</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('origin_location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->code }} &bull; {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                                    </option>
                                @endforeach
                            </select>
                            @error('origin_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pelabuhan / Hub Tujuan (Destination) <span class="text-danger">*</span></label>
                            <select name="destination_location_id" id="destLocation" class="form-select @error('destination_location_id') is-invalid @enderror" required>
                                <option value="">Pilih Lokasi Tujuan...</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('destination_location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->code }} &bull; {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                                    </option>
                                @endforeach
                            </select>
                            @error('destination_location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Klasifikasi Kargo <span class="text-danger">*</span></label>
                            <select name="cargo_type" class="form-select @error('cargo_type') is-invalid @enderror" required>
                                <option value="Container FCL" {{ old('cargo_type') === 'Container FCL' ? 'selected' : '' }}>Container FCL (Full Container Load)</option>
                                <option value="Container LCL" {{ old('cargo_type') === 'Container LCL' ? 'selected' : '' }}>Container LCL (Less than Container Load)</option>
                                <option value="Electronics & Tech" {{ old('cargo_type', 'Electronics & Tech') === 'Electronics & Tech' ? 'selected' : '' }}>Electronics & Precision Tech</option>
                                <option value="Perishable / Reefer" {{ old('cargo_type') === 'Perishable / Reefer' ? 'selected' : '' }}>Perishable / Temperature-Controlled</option>
                                <option value="Hazardous Material" {{ old('cargo_type') === 'Hazardous Material' ? 'selected' : '' }}>Hazardous Material (DG)</option>
                                <option value="Dry Bulk" {{ old('cargo_type') === 'Dry Bulk' ? 'selected' : '' }}>Dry Bulk & Raw Material</option>
                            </select>
                            @error('cargo_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">No. Referensi Customer / PO</label>
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" placeholder="Contoh: PO-2026-0988" value="{{ old('reference_number') }}">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Estimasi Keberangkatan (ETD) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="expected_departure" class="form-control @error('expected_departure') is-invalid @enderror" value="{{ old('expected_departure', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('expected_departure')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Estimasi Kedatangan (ETA) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="expected_arrival" class="form-control @error('expected_arrival') is-invalid @enderror" value="{{ old('expected_arrival', now()->addDays(14)->format('Y-m-d\TH:i')) }}" required>
                            @error('expected_arrival')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Instruksi Khusus / Catatan Pengiriman</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Catatan operasional penanganan kargo, dokumen khusus, atau nomor container...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Summary Metrics -->
            <div class="col-12 col-lg-5">
                <div class="card mb-4 bg-light">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Ringkasan Manifest Muatan</h5>

                    <div class="row g-3 text-center mb-3">
                        <div class="col-6">
                            <div class="p-3 border rounded bg-white">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Total Berat</small>
                                <span class="h4 fw-bold mb-0 text-dark" id="summaryTotalWeight">0.00</span>
                                <small class="text-muted">kg</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded bg-white">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Total Volume</small>
                                <span class="h4 fw-bold mb-0 text-dark" id="summaryTotalVolume">0.00</span>
                                <small class="text-muted">CBM</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded bg-white">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Total Kuantitas</small>
                                <span class="h4 fw-bold mb-0 text-dark" id="summaryTotalQty">0</span>
                                <small class="text-muted">unit</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded bg-white">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Declared Value</small>
                                <small class="text-muted">$</small>
                                <span class="h4 fw-bold mb-0 text-dark" id="summaryTotalValue">0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Nomor Shipment (format <code>CT-YYYY-XXXXXX</code>) akan digenerate otomatis secara unik dan checkpoint pelacakan awal di lokasi asal dibuat secara otomatis.
                    </div>
                </div>
            </div>

            <!-- Bottom Full-Width Column: Dynamic Manifest Line Items -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">2. Manifest Barang Kargo (Line Items)</h5>
                            <small class="text-muted">Daftar item komoditas yang dimuat dalam shipment ini.</small>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" id="addItemBtn">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Item Kargo
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%;">SKU / Part Number</th>
                                    <th style="width: 30%;">Deskripsi Barang <span class="text-danger">*</span></th>
                                    <th style="width: 12%;">Qty <span class="text-danger">*</span></th>
                                    <th style="width: 13%;">Berat (kg)</th>
                                    <th style="width: 13%;">Volume (CBM)</th>
                                    <th style="width: 12%;">Nilai ($)</th>
                                    <th style="width: 5%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                <tr class="item-row" data-index="0">
                                    <td>
                                        <input type="text" name="items[0][sku]" class="form-control form-control-sm font-monospace" placeholder="SKU-1001" value="{{ old('items.0.sku') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][description]" class="form-control form-control-sm item-desc" placeholder="Nama item / komoditas" value="{{ old('items.0.description', 'Industrial Microcontroller Components') }}" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" min="1" value="{{ old('items.0.quantity', 100) }}" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][weight_kg]" class="form-control form-control-sm item-weight" min="0" placeholder="0.00" value="{{ old('items.0.weight_kg', 25.5) }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][volume_cbm]" class="form-control form-control-sm item-volume" min="0" placeholder="0.00" value="{{ old('items.0.volume_cbm', 0.15) }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][declared_value]" class="form-control form-control-sm item-value" min="0" placeholder="0.00" value="{{ old('items.0.declared_value', 12500) }}">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('shipments.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Registrasikan Shipment
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 1;
    const itemsContainer = document.getElementById('itemsContainer');
    const addItemBtn = document.getElementById('addItemBtn');

    function recalculateSummary() {
        let totalQty = 0;
        let totalWeight = 0;
        let totalVolume = 0;
        let totalValue = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const weight = parseFloat(row.querySelector('.item-weight')?.value) || 0;
            const volume = parseFloat(row.querySelector('.item-volume')?.value) || 0;
            const value = parseFloat(row.querySelector('.item-value')?.value) || 0;

            totalQty += qty;
            totalWeight += (weight * qty);
            totalVolume += (volume * qty);
            totalValue += (value * qty);
        });

        document.getElementById('summaryTotalQty').textContent = totalQty.toLocaleString();
        document.getElementById('summaryTotalWeight').textContent = totalWeight.toFixed(2);
        document.getElementById('summaryTotalVolume').textContent = totalVolume.toFixed(2);
        document.getElementById('summaryTotalValue').textContent = totalValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    addItemBtn.addEventListener('click', function () {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = rowIndex;
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${rowIndex}][sku]" class="form-control form-control-sm font-monospace" placeholder="SKU-200${rowIndex}">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm item-desc" placeholder="Nama item / komoditas" required>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm item-qty" min="1" value="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][weight_kg]" class="form-control form-control-sm item-weight" min="0" placeholder="0.00" value="0.00">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][volume_cbm]" class="form-control form-control-sm item-volume" min="0" placeholder="0.00" value="0.00">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][declared_value]" class="form-control form-control-sm item-value" min="0" placeholder="0.00" value="0.00">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        itemsContainer.appendChild(tr);
        rowIndex++;
        updateRemoveButtons();
        recalculateSummary();
    });

    itemsContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-item-btn');
        if (btn) {
            const rows = itemsContainer.querySelectorAll('.item-row');
            if (rows.length > 1) {
                btn.closest('.item-row').remove();
                updateRemoveButtons();
                recalculateSummary();
            }
        }
    });

    itemsContainer.addEventListener('input', function (e) {
        if (e.target.matches('.item-qty, .item-weight, .item-volume, .item-value')) {
            recalculateSummary();
        }
    });

    function updateRemoveButtons() {
        const rows = itemsContainer.querySelectorAll('.item-row');
        rows.forEach(r => {
            const btn = r.querySelector('.remove-item-btn');
            if (btn) btn.disabled = (rows.length <= 1);
        });
    }

    recalculateSummary();
});
</script>
@endsection
