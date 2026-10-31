@props(['type' => null])

@if ($type && isset($slot) && $slot->isNotEmpty())
    <div class="alert alert-{{ $type }} alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background: rgba(255, 255, 255, 0.65); border: 1px solid var(--colors-hairline, #E5E0D5); border-left: 3px solid var(--colors-accent, #1A1A1C); color: #1A1A1C; border-radius: 12px;">
        @if ($type === 'success')
            <i class="bi bi-check-circle-fill fs-5 me-2" style="color: #15803D;"></i>
        @elseif ($type === 'danger' || $type === 'error')
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2" style="color: #DC2626;"></i>
        @else
            <i class="bi bi-info-circle-fill fs-5 me-2" style="color: #0284C7;"></i>
        @endif
        <div class="flex-grow-1 font-monospace small" style="color: #1A1A1C;">
            {{ $slot }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background: rgba(21, 128, 61, 0.08); border: 1px solid rgba(21, 128, 61, 0.25); border-left: 3px solid #15803D; color: #1A1A1C; border-radius: 12px;">
        <i class="bi bi-check-circle-fill fs-5 me-2" style="color: #15803D;"></i>
        <div class="flex-grow-1 font-monospace small" style="color: #1A1A1C;">
            {{ session('success') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.25); border-left: 3px solid #DC2626; color: #1A1A1C; border-radius: 12px;">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2" style="color: #DC2626;"></i>
        <div class="flex-grow-1 font-monospace small" style="color: #1A1A1C;">
            {{ session('error') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background: rgba(217, 119, 6, 0.08); border: 1px solid rgba(217, 119, 6, 0.25); border-left: 3px solid #D97706; color: #1A1A1C; border-radius: 12px;">
        <i class="bi bi-exclamation-circle-fill fs-5 me-2" style="color: #D97706;"></i>
        <div class="flex-grow-1 font-monospace small" style="color: #1A1A1C;">
            {{ session('warning') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-dismissible fade show mb-4" role="alert" style="background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.25); border-left: 3px solid #DC2626; color: #1A1A1C; border-radius: 12px;">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2" style="color: #DC2626;"></i>
            <strong class="font-monospace" style="color: #1A1A1C; font-weight: 700;">Terdapat kesalahan pengisian data:</strong>
        </div>
        <ul class="mb-0 ps-3 small font-monospace" style="color: #1A1A1C;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
