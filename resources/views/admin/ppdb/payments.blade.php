@extends('admin.layout')

@section('title', 'Verifikasi Pembayaran SPMB')
@section('header_title', 'Verifikasi Pembayaran Formulir SPMB')

@push('styles')
<style>
    /* Premium Summary Metric Cards */
    .summary-card {
        cursor: pointer;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem 1.25rem 1rem 1.25rem;
        box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        user-select: none;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s ease, background-color 0.2s ease;
    }
    .summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3.5px;
        background: transparent;
        transition: height 0.2s ease;
    }
    .summary-card.card-warning::before {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }
    .summary-card.card-success::before {
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    .summary-card.card-danger::before {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }
    .summary-card.card-primary::before {
        background: linear-gradient(90deg, #005ab4, #38bdf8);
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08), 0 4px 8px -2px rgba(15, 23, 42, 0.03);
        border-color: #cbd5e1;
    }
    .summary-card:active {
        transform: translateY(-1px);
    }
    .summary-card.active-card {
        border-color: #005ab4 !important;
        box-shadow: 0 0 0 2px rgba(0, 90, 180, 0.18), 0 8px 20px -4px rgba(0, 90, 180, 0.12) !important;
        background-color: #f8fbff;
    }
    .summary-card.active-card::before {
        height: 4.5px;
    }
    .summary-card-label {
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .summary-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .summary-card-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.02em;
        color: #0f172a;
    }
    .summary-card-revenue {
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: #005ab4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .summary-card-footer {
        border-top: 1px solid #f1f5f9;
        margin-top: 0.75rem;
        padding-top: 0.65rem;
    }

    /* Filter Nav Pills Animation */
    .filter-tab-btn {
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 9999px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        color: #475569;
        display: inline-flex;
        align-items: center;
    }
    .filter-tab-btn:hover:not(.active) {
        background-color: #edf2f7;
        color: #0f172a;
        transform: translateY(-1px);
    }
    .filter-tab-btn.active {
        background-color: #005ab4 !important;
        color: #ffffff !important;
        border-color: #005ab4 !important;
        box-shadow: 0 4px 14px rgba(0, 90, 180, 0.28);
        transform: translateY(-1px);
    }
    .filter-tab-btn.active .material-symbols-outlined {
        color: #ffffff !important;
    }

    /* Animated Table Rows */
    .payment-row {
        transition: opacity 0.22s cubic-bezier(0.4, 0, 0.2, 1), transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 1;
        transform: translateY(0);
    }
    .payment-row.is-hiding {
        opacity: 0;
        transform: translateY(-6px);
    }
    .payment-row.is-hidden {
        display: none !important;
    }
    .payment-row.is-entering {
        opacity: 0;
        transform: translateY(8px);
    }

    /* Badge Counter Animation */
    .counter-pulse {
        animation: badgePulse 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes badgePulse {
        0% { transform: scale(0.92); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1); }
    }
</style>
@endpush

@section('content')
@php 
    $bank = \App\Support\BankSetting::get();
    $activeFilter = $filterStatus ?? request()->query('status', 'all');
    if (!in_array($activeFilter, ['all', 'pending', 'paid', 'rejected', 'unpaid'])) {
        $activeFilter = 'all';
    }
@endphp

<!-- Rekening Pembayaran Setting Card -->
<div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 22px;">account_balance</span>
            <h6 class="fw-bold mb-0 text-dark">Rekening Tujuan Pembayaran Pendaftaran</h6>
        </div>
        <span class="badge bg-light text-muted border small d-none d-sm-inline">Ditampilkan di Portal Calon Siswa</span>
    </div>
    @if(session('bank_saved'))
        <div class="alert alert-success py-2 d-flex align-items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-success" style="font-size: 18px;">check_circle</span>
            <span>{{ session('bank_saved') }}</span>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger py-2 d-flex align-items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-danger" style="font-size: 18px;">error</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.ppdb.bank-setting') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-3">
            <label class="form-label small mb-1 fw-semibold text-secondary">Nama Bank</label>
            <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name', $bank['bank_name']) }}" placeholder="cth: Bank Syariah Indonesia" required>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1 fw-semibold text-secondary">No. Rekening</label>
            <input type="text" name="account_number" class="form-control form-control-sm" value="{{ old('account_number', $bank['account_number']) }}" placeholder="cth: 7123456789" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1 fw-semibold text-secondary">Atas Nama Rekening</label>
            <input type="text" name="account_holder" class="form-control form-control-sm" value="{{ old('account_holder', $bank['account_holder']) }}" placeholder="cth: SPMB SMP Al-Muhajirin" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold d-flex align-items-center justify-content-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                <span>Simpan</span>
            </button>
        </div>
    </form>
</div>

<!-- Summary Cards (Interactive & Clickable to Filter) -->
<div class="row g-3 mb-4">
    <!-- Card 1: Perlu Verifikasi -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="summary-card summary-clickable card-warning h-100 {{ $activeFilter === 'pending' ? 'active-card' : '' }}" 
             onclick="window.location.href='{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'pending', 'page' => 1])) }}'"
             role="button"
             tabindex="0"
             title="Klik untuk memfilter transaksi menunggu verifikasi">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="summary-card-label text-secondary">PERLU VERIFIKASI</span>
                    <div class="summary-card-icon bg-warning-subtle text-warning">
                        <span class="material-symbols-outlined" style="font-size: 20px;">hourglass_top</span>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <div class="summary-card-value">{{ $stats['pending'] ?? 0 }}</div>
                    <span class="text-muted small fw-medium">murid</span>
                </div>
            </div>
            <div class="summary-card-footer d-flex align-items-center justify-content-between">
                <span class="text-muted small" style="font-size: 12px;">Bukti baru diunggah</span>
                <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold rounded-pill px-2 py-1" style="font-size: 11px;">
                    <span class="material-symbols-outlined align-middle" style="font-size: 13px;">schedule</span> Pending
                </span>
            </div>
        </div>
    </div>

    <!-- Card 2: Sudah Lunas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="summary-card summary-clickable card-success h-100 {{ $activeFilter === 'paid' ? 'active-card' : '' }}" 
             onclick="window.location.href='{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'paid', 'page' => 1])) }}'"
             role="button"
             tabindex="0"
             title="Klik untuk memfilter transaksi lunas">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="summary-card-label text-secondary">SUDAH LUNAS</span>
                    <div class="summary-card-icon bg-success-subtle text-success">
                        <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <div class="summary-card-value">{{ $stats['paid'] ?? 0 }}</div>
                    <span class="text-muted small fw-medium">murid</span>
                </div>
            </div>
            <div class="summary-card-footer d-flex align-items-center justify-content-between">
                <span class="text-muted small" style="font-size: 12px;">Sah terverifikasi</span>
                <span class="badge bg-success-subtle text-success fw-semibold rounded-pill px-2 py-1" style="font-size: 11px;">
                    <span class="material-symbols-outlined align-middle" style="font-size: 13px;">verified</span> Lunas
                </span>
            </div>
        </div>
    </div>

    <!-- Card 3: Ditolak -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="summary-card summary-clickable card-danger h-100 {{ $activeFilter === 'rejected' ? 'active-card' : '' }}" 
             onclick="window.location.href='{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'rejected', 'page' => 1])) }}'"
             role="button"
             tabindex="0"
             title="Klik untuk memfilter transaksi ditolak">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="summary-card-label text-secondary">DITOLAK</span>
                    <div class="summary-card-icon bg-danger-subtle text-danger">
                        <span class="material-symbols-outlined" style="font-size: 20px;">cancel</span>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <div class="summary-card-value">{{ $stats['rejected'] ?? 0 }}</div>
                    <span class="text-muted small fw-medium">murid</span>
                </div>
            </div>
            <div class="summary-card-footer d-flex align-items-center justify-content-between">
                <span class="text-muted small" style="font-size: 12px;">Bukti tidak valid</span>
                <span class="badge bg-danger-subtle text-danger fw-semibold rounded-pill px-2 py-1" style="font-size: 11px;">
                    <span class="material-symbols-outlined align-middle" style="font-size: 13px;">block</span> Ditolak
                </span>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Dana Lunas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="summary-card summary-clickable card-primary h-100 {{ $activeFilter === 'paid' ? 'active-card' : '' }}" 
             onclick="window.location.href='{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'paid', 'page' => 1])) }}'"
             role="button"
             tabindex="0"
             title="Klik untuk melihat seluruh transaksi lunas">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="summary-card-label text-secondary">TOTAL DANA LUNAS</span>
                    <div class="summary-card-icon bg-primary-subtle text-primary">
                        <span class="material-symbols-outlined" style="font-size: 20px;">account_balance_wallet</span>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-1 mb-1">
                    <div class="summary-card-revenue">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="summary-card-footer d-flex align-items-center justify-content-between">
                <span class="text-muted small" style="font-size: 12px;">Kas formulir masuk</span>
                <span class="badge bg-primary-subtle text-primary fw-semibold rounded-pill px-2 py-1" style="font-size: 11px;">
                    <span class="material-symbols-outlined align-middle" style="font-size: 13px;">payments</span> {{ $stats['paid'] ?? 0 }} Murid
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Telegram Bot Notification & 1-Click Verification Banner -->
<div class="card border-0 shadow-sm rounded-3 mb-4 p-3 p-md-3" style="border: 1px solid #bae6fd !important; background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background-color: #e0f2fe; color: #0284c7;">
                <span class="material-symbols-outlined" style="font-size: 24px;">smart_toy</span>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h6 class="fw-bold mb-0 text-dark">Verifikasi Pembayaran via Telegram Bot Aktif</h6>
                    <span class="badge bg-success-subtle text-success fw-semibold border" style="font-size: 11px;">
                        <span class="material-symbols-outlined align-middle" style="font-size: 13px;">check_circle</span> Online
                    </span>
                    <span class="badge bg-white text-primary border" style="font-size: 11px;">@paymen_ppdb_bot</span>
                </div>
                <div class="text-muted small mt-1">
                    Setiap bukti transfer yang diunggah murid otomatis diteruskan ke grup Telegram panitia lengkap dengan tombol <strong>[✅ Verifikasi Lunas]</strong> dan <strong>[❌ Tolak Bukti]</strong> 1-klik tanpa perlu login ke web.
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <a href="https://t.me/paymen_ppdb_bot" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 fw-semibold px-3 py-1.5 rounded-pill shadow-sm bg-white">
                <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                <span>Buka Bot Telegram</span>
            </a>
        </div>
    </div>
</div>

<!-- Nav Pills & Live Search Toolbar -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white p-3 p-md-4" id="paymentToolbar">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <!-- Status Filter Tabs -->
        <ul class="nav nav-pills gap-1 flex-wrap" id="paymentStatusTabs">
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold filter-tab-btn {{ $activeFilter === 'all' ? 'active' : '' }}" 
                   href="{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'all', 'page' => 1])) }}" 
                   style="font-size: 13px;">
                    <span class="material-symbols-outlined align-middle me-1" style="font-size: 16px;">list_alt</span>
                    Semua ({{ $stats['total'] ?? 0 }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold filter-tab-btn {{ $activeFilter === 'pending' ? 'active' : '' }}" 
                   href="{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'pending', 'page' => 1])) }}" 
                   style="font-size: 13px;">
                    <span class="material-symbols-outlined align-middle me-1 text-warning" style="font-size: 16px;">hourglass_top</span>
                    Menunggu ({{ $stats['pending'] ?? 0 }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold filter-tab-btn {{ $activeFilter === 'paid' ? 'active' : '' }}" 
                   href="{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'paid', 'page' => 1])) }}" 
                   style="font-size: 13px;">
                    <span class="material-symbols-outlined align-middle me-1 text-success" style="font-size: 16px;">check_circle</span>
                    Lunas ({{ $stats['paid'] ?? 0 }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold filter-tab-btn {{ $activeFilter === 'rejected' ? 'active' : '' }}" 
                   href="{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'rejected', 'page' => 1])) }}" 
                   style="font-size: 13px;">
                    <span class="material-symbols-outlined align-middle me-1 text-danger" style="font-size: 16px;">cancel</span>
                    Ditolak ({{ $stats['rejected'] ?? 0 }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold filter-tab-btn {{ $activeFilter === 'unpaid' ? 'active' : '' }}" 
                   href="{{ route('admin.ppdb.payments', array_merge(request()->query(), ['status' => 'unpaid', 'page' => 1])) }}" 
                   style="font-size: 13px;">
                    <span class="material-symbols-outlined align-middle me-1 text-secondary" style="font-size: 16px;">money_off</span>
                    Belum Bayar ({{ $stats['unpaid'] ?? 0 }})
                </a>
            </li>
        </ul>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.ppdb.payments') }}" class="d-flex gap-2 w-100 position-relative" style="max-width: 320px;">
            <input type="hidden" name="status" value="{{ $activeFilter }}">
            <input type="hidden" name="per_page" value="{{ $payments->perPage() }}">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <span class="material-symbols-outlined" style="font-size: 17px;">search</span>
                </span>
                <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Cari Nama, NIK, No HP, Bank..." value="{{ $filters['search'] ?? '' }}">
                @if(!empty($filters['search']))
                    <a href="{{ route('admin.ppdb.payments', array_merge(request()->query(), ['search' => null, 'page' => 1])) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" title="Hapus pencarian">
                        <span class="material-symbols-outlined align-middle" style="font-size: 16px;">close</span>
                    </a>
                @endif
                <button class="btn btn-sm btn-primary" type="submit">
                    <span class="material-symbols-outlined align-middle" style="font-size: 16px;">search</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill" id="visibleCountBadge" style="font-size: 12px;">
                {{ $payments->total() }} Transaksi Ditemukan
            </span>
            <span class="text-muted small d-none d-sm-inline">Daftar biaya formulir pendaftaran SPMB</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="paymentsTable" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th style="width: 48px;" class="ps-3 ps-md-4 text-center">No</th>
                    <th>Calon Siswa</th>
                    <th>Email & NIK</th>
                    <th>Nominal</th>
                    <th>Metode & Pengirim</th>
                    <th>Bukti Bayar</th>
                    <th>Status</th>
                    <th>Tanggal Masuk</th>
                    <th style="text-align: right; width: 140px;" class="pe-3 pe-md-4">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $index => $pay)
                    @php
                        $pStatus = $pay['payment_status'] ?? 'unpaid';
                        $pMethod = $pay['payment_method'] ?? 'transfer';
                        $acc = $pay['account'] ?? [];
                        $proofPath = $pay['payment_proof_path'] ?? null;
                    @endphp
                    <tr>
                        <td class="ps-3 ps-md-4 text-center text-muted fw-semibold">
                            {{ ($payments->firstItem() ?? 1) + $index }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $acc['full_name'] ?? ($pay['full_name'] ?? 'Calon Siswa') }}</div>
                            @if(!empty($pay['phone']) && $pay['phone'] !== '-')
                                <div class="text-muted small" style="font-size: 11.5px;">
                                    <span class="material-symbols-outlined align-middle" style="font-size: 13px;">call</span>
                                    {{ $pay['phone'] }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="text-dark">{{ $acc['email'] ?? ($pay['email'] ?? '-') }}</div>
                            <div class="text-muted small">NIK: {{ $acc['nik'] ?? ($pay['nik'] ?? '-') }}</div>
                        </td>
                        <td>
                            @php
                                $itemAmount = isset($pay['payment_amount']) && $pay['payment_amount'] !== '' && $pay['payment_amount'] !== null ? (float)$pay['payment_amount'] : 400000;
                            @endphp
                            @if($itemAmount == 0 && ($pay['payment_status'] ?? '') === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 d-inline-flex align-items-center gap-1" title="Bebas Biaya / Jalur Prestasi">
                                    <span class="material-symbols-outlined" style="font-size:13px;">workspace_premium</span>
                                    Rp 0 (Prestasi)
                                </span>
                            @else
                                <span class="fw-bold text-dark">Rp {{ number_format($itemAmount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($pMethod === 'cash')
                                <span class="badge py-1 px-2" style="background:#ccfbf1;color:#0f766e;border:1px solid #99f6e4;">
                                    <span class="material-symbols-outlined align-middle me-1" style="font-size:13px;">payments</span>
                                    Tunai (Cash)
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2">
                                    <span class="material-symbols-outlined align-middle me-1" style="font-size:13px;">account_balance</span>
                                    Transfer (TF)
                                </span>
                            @endif
                            @if(!empty($pay['sender_bank_name']) || !empty($pay['sender_name']))
                                <div class="text-muted small mt-1" style="font-size: 11.5px;">
                                    <span class="fw-semibold text-secondary">{{ $pay['sender_bank_name'] ?? 'Bank' }}</span>
                                    @if(!empty($pay['sender_name']))
                                        &bull; a.n <span class="text-dark">{{ $pay['sender_name'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($proofPath)
                                <a href="{{ ppdb_proof_url($proofPath, $backendUrl) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none py-1 px-2" title="Klik untuk lihat berkas bukti">
                                    <span class="material-symbols-outlined align-middle" style="font-size:14px;">image</span>
                                    {{ $pMethod === 'cash' ? 'Kuitansi Tunai' : 'Struk Transfer' }}
                                </a>
                            @else
                                <span class="text-muted small">Tidak Ada Bukti</span>
                            @endif
                        </td>
                        <td>
                            @if($pStatus === 'paid')
                                <span class="badge bg-success-subtle text-success py-1 px-2">Lunas</span>
                            @elseif(in_array($pStatus, ['pending', 'pending_verification']))
                                <span class="badge bg-warning-subtle text-warning py-1 px-2">Menunggu</span>
                            @elseif($pStatus === 'rejected')
                                <span class="badge bg-danger-subtle text-danger py-1 px-2">Ditolak</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary py-1 px-2">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="small">
                            @php
                                $trxTime = !empty($pay['updated_at']) && $pay['payment_status'] !== 'unpaid'
                                    ? $pay['updated_at']
                                    : ($pay['created_at'] ?? 'now');
                            @endphp
                            <div class="fw-semibold text-dark">{{ date('d/m/Y H:i', strtotime($trxTime)) }} WIB</div>
                            @if(!empty($pay['created_at']) && $trxTime !== $pay['created_at'])
                                <div class="text-muted" style="font-size: 10.5px;">Daftar: {{ date('d/m/Y', strtotime($pay['created_at'])) }}</div>
                            @endif
                        </td>
                        <td class="pe-3 pe-md-4 text-end">
                            <button type="button" class="btn btn-sm btn-primary py-1 px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $pay['id'] }}" style="font-size: 12.5px;">
                                {{ in_array($pStatus, ['pending', 'pending_verification']) ? 'Verifikasi' : 'Kelola' }}
                            </button>

                            <!-- Modal Verifikasi Detail -->
                            <div class="modal fade" id="verifyModal{{ $pay['id'] }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered text-start">
                                    <div class="modal-content border-0 shadow rounded-3">
                                        <div class="modal-header border-bottom py-3">
                                            <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-primary">payments</span>
                                                Verifikasi Pembayaran SPMB
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <form action="{{ route('admin.ppdb.verify-payment', $pay['id']) }}" method="POST">
                                            @csrf
                                            <div class="modal-body p-3 p-md-4">
                                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                                    <div class="fw-bold text-dark mb-1">{{ $acc['full_name'] ?? ($pay['full_name'] ?? '-') }}</div>
                                                    <div class="text-muted small">NIK: {{ $acc['nik'] ?? ($pay['nik'] ?? '-') }} &bull; Email: {{ $acc['email'] ?? ($pay['email'] ?? '-') }}</div>
                                                    @if(!empty($pay['phone']) && $pay['phone'] !== '-')
                                                        <div class="text-muted small mt-1">No. HP / WhatsApp: {{ $pay['phone'] }}</div>
                                                    @endif
                                                    @if(!empty($pay['sender_bank_name']) || !empty($pay['sender_name']))
                                                        <div class="mt-2 pt-2 border-top text-muted small">
                                                            <span class="fw-semibold text-dark">Rekening Pengirim:</span> 
                                                            {{ $pay['sender_bank_name'] ?? '-' }} 
                                                            @if(!empty($pay['sender_name']))
                                                                (a.n {{ $pay['sender_name'] }})
                                                            @endif
                                                        </div>
                                                    @endif
                                                    <div class="text-muted small mt-2 pt-2 border-top">
                                                        <span class="fw-semibold">Waktu Masuk:</span> {{ date('d/m/Y H:i', strtotime($trxTime)) }} WIB
                                                        @if(!empty($pay['payment_verified_at']))
                                                            <br><span class="fw-semibold">Diverifikasi:</span> {{ date('d/m/Y H:i', strtotime($pay['payment_verified_at'])) }} WIB (oleh: {{ $pay['payment_verified_by'] ?? 'Admin' }})
                                                        @endif
                                                    </div>
                                                </div>

                                                @if($proofPath)
                                                    <div class="mb-3 text-center">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">
                                                            Berkas {{ $pMethod === 'cash' ? 'Kuitansi Tunai (Cash)' : 'Bukti Transfer (TF)' }}
                                                        </div>
                                                        <a href="{{ ppdb_proof_url($proofPath, $backendUrl) }}" target="_blank" class="d-inline-block border rounded-3 p-1 bg-light">
                                                            <img src="{{ ppdb_proof_url($proofPath, $backendUrl) }}" alt="Bukti Pembayaran" style="max-height: 240px; max-width: 100%; object-fit: contain;" class="rounded">
                                                        </a>
                                                        <div class="mt-1">
                                                            <a href="{{ ppdb_proof_url($proofPath, $backendUrl) }}" target="_blank" class="small fw-semibold text-primary text-decoration-none">
                                                                Buka Ukuran Penuh &rarr;
                                                            </a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning py-2 small mb-3">
                                                        Pendaftar belum mengunggah berkas bukti pembayaran.
                                                    </div>
                                                @endif

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark small mb-1">Metode Pembayaran:</label>
                                                    <select class="form-select form-select-sm" name="payment_method">
                                                        <option value="transfer" {{ $pMethod === 'transfer' ? 'selected' : '' }}>Transfer Bank (TF)</option>
                                                        <option value="cash" {{ $pMethod === 'cash' ? 'selected' : '' }}>Tunai / Cash di Loket</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark small mb-1">Keputusan Status Pembayaran:</label>
                                                    <select class="form-select form-select-sm" name="payment_status" required>
                                                        <option value="paid" {{ $pStatus === 'paid' ? 'selected' : '' }}>Setujui: Lunas (PAID)</option>
                                                        <option value="rejected" {{ $pStatus === 'rejected' ? 'selected' : '' }}>Tolak: Tidak Valid (REJECTED)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark small mb-1">Nominal Terverifikasi (Rp):</label>
                                                    <input type="number" class="form-control form-control-sm" name="payment_amount" value="{{ (int)(isset($pay['payment_amount']) && $pay['payment_amount'] !== '' && $pay['payment_amount'] !== null ? $pay['payment_amount'] : 400000) }}" min="0" step="1000" required>
                                                    <div class="form-text small" style="font-size: 11px;">
                                                        Isi <strong>0</strong> jika pendaftar lolos jalur prestasi / beasiswa (bebas biaya).
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top bg-light py-2">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Keputusan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 36px;">payments</span>
                            <div class="small">Belum ada data transaksi pembayaran yang tercatat.</div>
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    @include('admin.ppdb.partials.pagination', ['paginator' => $payments])
</div>
@endsection
