@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')
@section('page_heading', 'Tambah Pengguna Baru')

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    @include('components.alert')

    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Tambah Pengguna Baru</h4>
                <p class="text-muted small mb-0">Daftarkan akun pengguna baru dengan hak akses yang terotorisasi.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <div class="card p-4">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           placeholder="Contoh: Budi Santoso" 
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Alamat Email <span class="text-danger">*</span></label>
                    <input type="email" 
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           placeholder="budi@example.com" 
                           value="{{ old('email') }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Kata Sandi (Password) <span class="text-danger">*</span></label>
                    <input type="password" 
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Minimal 6 karakter" 
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                    <input type="password" 
                           name="password_confirmation" 
                           class="form-control" 
                           placeholder="Ulangi kata sandi" 
                           required>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Peran Akses (Role) <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Customer (Pengirim/Penerima)</option>
                        <option value="operator" {{ old('role') === 'operator' ? 'selected' : '' }}>Logistics Operator (Petugas)</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh)</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Nomor Telepon</label>
                    <input type="text" 
                           name="phone" 
                           class="form-control @error('phone') is-invalid @enderror" 
                           placeholder="+62 812 3456 7890" 
                           value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Nama Perusahaan / Organisasi</label>
                    <input type="text" 
                           name="company_name" 
                           class="form-control @error('company_name') is-invalid @enderror" 
                           placeholder="PT Global Nusantara Kargo" 
                           value="{{ old('company_name') }}">
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i>
                        <span>Buat Akun Pengguna</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
