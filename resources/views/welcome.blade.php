@extends('layouts.app')

@section('title', 'Penerimaan Murid Baru — SMPS2 Al-Muhajirin')

@push('styles')
<style>
/* ========================================================
   LANDING PAGE — PROGRAM SECTION & YOUTUBE SHOWCASE STYLES
   (Disematkan langsung agar tampilan Program & YouTube Showcase
    langsung aktif saat paste welcome.blade.php di panel hosting)
   ======================================================== */
.lp-program-section {
    width: 100%;
    padding: 5rem 0;
    background: var(--lp-surface-pure);
    border-top: 1px solid var(--lp-surface-container);
    position: relative;
}

/* 4-Item Program Selector Grid */
.lp-program-selector-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    margin-top: 2.5rem;
}

@media (min-width: 640px) {
    .lp-program-selector-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .lp-program-selector-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.lp-prog-select-card {
    background: var(--lp-surface);
    border-radius: 18px;
    padding: 1.5rem;
    border: 2px solid var(--lp-surface-container);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    cursor: pointer;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    text-align: left;
    user-select: none;
}

.lp-prog-select-card:hover {
    background: var(--lp-surface-pure);
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
}

.lp-prog-select-card.active {
    background: #ffffff;
    box-shadow: 0 14px 32px rgba(0, 90, 180, 0.1);
    transform: translateY(-2px);
}

/* Theme Borders & Accents for Active State */
.lp-prog-select-card.theme-tahfizh.active {
    border-color: var(--lp-green);
}
.lp-prog-select-card.theme-ict.active {
    border-color: var(--lp-primary);
}
.lp-prog-select-card.theme-bahasa.active {
    border-color: var(--lp-yellow-dark);
}
.lp-prog-select-card.theme-reguler.active {
    border-color: var(--lp-primary-dark);
}

.lp-prog-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.lp-prog-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.lp-prog-card-badge {
    font-size: 10px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.lp-prog-select-card h3 {
    font-size: 17px;
    font-weight: 800;
    color: var(--lp-on-surface);
    margin: 0 0 6px 0;
    line-height: 1.3;
}

.lp-prog-select-card p {
    font-size: 12.5px;
    color: var(--lp-on-surface-variant);
    margin: 0 0 1.25rem 0;
    line-height: 1.55;
    flex-grow: 1;
}

.lp-prog-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.85rem;
    border-top: 1px solid var(--lp-surface-container);
    font-size: 11.5px;
    font-weight: 700;
}

.lp-prog-active-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 700;
    opacity: 0;
    transition: opacity 0.2s;
}

.lp-prog-select-card.active .lp-prog-active-indicator {
    opacity: 1;
}

/* Detailed Program Panels */
.lp-prog-detail-wrapper {
    margin-top: 2rem;
}

.lp-prog-panel {
    display: none;
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid var(--lp-surface-container);
    box-shadow: 0 8px 30px rgba(0, 90, 180, 0.05);
    padding: 1.75rem;
    animation: lpFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.lp-prog-panel.active {
    display: block;
}

@keyframes lpFadeUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (min-width: 768px) {
    .lp-prog-panel {
        padding: 2.5rem;
    }
}

/* Panel Header */
.lp-panel-header {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding-bottom: 1.75rem;
    border-bottom: 1px solid var(--lp-surface-container);
}

@media (min-width: 768px) {
    .lp-panel-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.lp-panel-header-left {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.lp-panel-avatar {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.lp-panel-title-area h3 {
    font-size: 21px;
    font-weight: 800;
    color: var(--lp-on-surface);
    margin: 0 0 4px 0;
    line-height: 1.25;
}

.lp-panel-title-area p {
    font-size: 13.5px;
    color: var(--lp-on-surface-variant);
    margin: 0;
    max-width: 650px;
    line-height: 1.5;
}

.lp-panel-stat-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.lp-panel-stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 700;
    background: var(--lp-surface);
    border: 1px solid var(--lp-surface-container);
    color: var(--lp-on-surface);
}

/* Target Lulusan Milestones (Kelas 7, 8, 9) */
.lp-milestone-section {
    margin-top: 2rem;
}

.lp-section-subheading {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--lp-primary);
    margin-bottom: 1.25rem;
}

.lp-milestone-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

@media (min-width: 768px) {
    .lp-milestone-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.lp-milestone-card {
    background: var(--lp-surface);
    border: 1px solid var(--lp-surface-container);
    border-radius: 18px;
    padding: 1.5rem;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}

.lp-milestone-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
}

.lp-milestone-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 6px;
    margin-bottom: 10px;
    letter-spacing: 0.04em;
}

.lp-milestone-card h4 {
    font-size: 15.5px;
    font-weight: 800;
    color: var(--lp-on-surface);
    margin: 0 0 8px 0;
    line-height: 1.35;
}

.lp-milestone-card p {
    font-size: 12px;
    color: var(--lp-on-surface-variant);
    line-height: 1.55;
    margin: 0;
}

/* Feature Bento / Dual Grid */
.lp-prog-grid-dual {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-top: 2rem;
}

@media (min-width: 992px) {
    .lp-prog-grid-dual {
        grid-template-columns: 7fr 5fr;
    }
}

.lp-prog-box {
    background: var(--lp-surface);
    border: 1px solid var(--lp-surface-container);
    border-radius: 18px;
    padding: 1.5rem;
}

.lp-prog-box-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 1.25rem;
}

.lp-prog-box-header h4 {
    font-size: 15px;
    font-weight: 800;
    color: var(--lp-on-surface);
    margin: 0;
}

/* Numbered Chips Grid for Programs */
.lp-chips-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

@media (min-width: 576px) {
    .lp-chips-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.lp-chip-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #ffffff;
    border: 1px solid var(--lp-surface-container);
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--lp-on-surface);
    transition: background 0.15s, border-color 0.15s;
}

.lp-chip-item:hover {
    background: var(--lp-surface-pure);
    border-color: rgba(0, 90, 180, 0.25);
}

.lp-chip-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 6px;
    background: var(--lp-surface-container-low);
    color: var(--lp-primary);
    font-size: 10.5px;
    font-weight: 800;
    flex-shrink: 0;
}

/* Quality Assurance Bento Cards (Kelas Bahasa) */
.lp-qa-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

@media (min-width: 640px) {
    .lp-qa-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.lp-qa-card {
    background: #ffffff;
    border: 1px solid var(--lp-surface-container);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.lp-qa-card:hover {
    border-color: rgba(234, 179, 8, 0.4);
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.lp-qa-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--lp-yellow-light);
    color: var(--lp-yellow-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    margin-top: 1px;
}

.lp-qa-card h5 {
    font-size: 13px;
    font-weight: 700;
    color: var(--lp-on-surface);
    margin: 0 0 2px 0;
}

.lp-qa-card p {
    font-size: 11.5px;
    color: var(--lp-on-surface-variant);
    margin: 0;
    line-height: 1.45;
}

/* Time Cycle Blocks for Bahasa (Harian, Mingguan, Bulanan, Semester, Tahunan) */
.lp-cycle-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.lp-cycle-row {
    background: #ffffff;
    border: 1px solid var(--lp-surface-container);
    border-radius: 12px;
    padding: 10px 14px;
}

.lp-cycle-header {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 6px;
}

.lp-cycle-items {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.lp-cycle-badge {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 600;
    background: var(--lp-surface-subtle);
    color: var(--lp-on-surface);
    border: 1px solid rgba(0,0,0,0.04);
}

/* Action Bar inside Panel */
.lp-panel-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--lp-surface-container);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.lp-btn-panel-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

/* YOUTUBE SHOWCASE COMPONENT */
.lp-yt-showcase-card {
    margin-top: 3.5rem;
    background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);
    border-radius: 26px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    position: relative;
}

.lp-yt-showcase-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    padding: 1.75rem;
    align-items: center;
}

@media (min-width: 992px) {
    .lp-yt-showcase-grid {
        grid-template-columns: 6fr 6fr;
        padding: 2.75rem;
        gap: 3rem;
    }
}

.lp-yt-player-wrap {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
    background: #000;
}

.lp-yt-responsive-ratio {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
}

.lp-yt-responsive-ratio iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.lp-yt-badge-floating {
    position: absolute;
    bottom: 12px;
    left: 12px;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    pointer-events: none;
}

.lp-yt-dot-live {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ef4444;
    box-shadow: 0 0 8px #ef4444;
}

.lp-yt-info-pane {
    color: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.lp-yt-channel-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.05em;
    padding: 5px 12px;
    border-radius: 9999px;
    width: fit-content;
    margin-bottom: 1rem;
}

.lp-yt-title {
    font-size: 22px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 0.85rem 0;
    line-height: 1.3;
}

@media (min-width: 768px) {
    .lp-yt-title {
        font-size: 26px;
    }
}

.lp-yt-desc {
    font-size: 13.5px;
    color: #cbd5e1;
    line-height: 1.65;
    margin: 0 0 1.5rem 0;
}

.lp-yt-features-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 1.75rem;
}

.lp-yt-feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #e2e8f0;
    font-weight: 600;
}

.lp-yt-feature-item .material-symbols-outlined {
    font-size: 18px;
    color: #38bdf8;
    flex-shrink: 0;
}

.lp-yt-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}

.lp-yt-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ef4444;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 700;
    padding: 10px 20px;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
    transition: all 0.2s;
}

.lp-yt-btn-primary:hover {
    background: #dc2626;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(239, 68, 68, 0.5);
}

.lp-yt-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.18);
    font-size: 13.5px;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.2s;
}

.lp-yt-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.lp-yt-tip {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 1.25rem;
    margin-bottom: 0;
}


</style>
@endpush

@section('content')
<div class="lp-main">

    {{-- ============================================================ --}}
    {{-- SECTION 1: HERO --}}
    {{-- ============================================================ --}}
    <div class="lp-hero-wrap" id="beranda" style="position:relative;background-color:var(--lp-surface);">
        {{-- Background Foto Sekolah dengan Overlay Elegan --}}
        <div class="lp-hero-bg-photo" style="position:absolute;inset:0;background-image:url('{{ asset('images/konten/sekolah.jpg') }}');background-size:cover;background-position:center 45%;background-repeat:no-repeat;pointer-events:none;z-index:0;"></div>
        <div class="lp-hero-bg-overlay" style="position:absolute;inset:0;background:linear-gradient(90deg, rgba(248,250,255,0.92) 0%, rgba(248,250,255,0.80) 45%, rgba(248,250,255,0.40) 80%, rgba(248,250,255,0.55) 100%), linear-gradient(180deg, rgba(248,250,255,0.5) 0%, rgba(248,250,255,0.15) 50%, var(--lp-surface) 100%);pointer-events:none;z-index:1;"></div>

        <div class="lp-hero-glow-1" style="z-index:2;"></div>
        <div class="lp-hero-glow-2" style="z-index:2;"></div>
        <div class="lp-hero-glow-3" style="z-index:2;"></div>

        <div class="lp-hero-inner" style="position:relative;z-index:3;">
            {{-- Left Column --}}
            <div style="display:flex;flex-direction:column;align-items:flex-start;gap:1rem;">

                {{-- Headline --}}
                <h1 class="lp-hero-title">
                    Penerimaan Murid Baru<br>
                    <span class="lp-hero-title-gradient">SMPS2 Al-Muhajirin</span>
                </h1>

                {{-- Subtitle --}}
                <p class="lp-hero-subtitle">
                    Mulai langkah pendidikan terbaik bersama lingkungan belajar yang berkarakter islami, religius, unggul akademik &amp; teknologi, serta siap menghadapi tantangan masa depan.
                </p>

                {{-- CTA Buttons --}}
                <div class="lp-hero-ctas">
                    @if(session()->has('api_token'))
                        <a class="lp-btn-primary" href="{{ route('dashboard') }}">
                            <span class="material-symbols-outlined" style="font-size:20px;">dashboard</span>
                            <span>Buka Dashboard Saya</span>
                        </a>
                    @else
                        <a class="lp-btn-primary" href="{{ route('register') }}">
                            <span>Daftar Sekarang</span>
                            <span class="material-symbols-outlined" style="font-size:20px;">arrow_forward</span>
                        </a>
                        <a class="lp-btn-secondary" href="#persyaratan">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-primary);">upload_file</span>
                            <span>Cek Berkas Softfile</span>
                        </a>
                        <a class="lp-btn-green" href="https://wa.me/6287821055283" target="_blank" rel="noopener">
                            <span class="material-symbols-outlined" style="font-size:19px;">chat</span>
                            <span>Konsultasi WA</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Right Column: Hero Card --}}
            <div style="margin-top:1rem;">
                <div class="lp-hero-card">
                    <div class="lp-hero-img-wrap">
                        <img src="{{ asset('images/konten/banner.jpg') }}" alt="Siswa SMP Full Day Al-Muhajirin berprestasi dan berkarakter">
                        <div class="lp-hero-img-overlay"></div>
                        <div class="lp-hero-img-badge">
                            <span class="material-symbols-outlined" style="font-size:16px;color:var(--lp-green);">check_circle</span>
                            Pendaftaran Online 2027/2028
                        </div>
                        <div class="lp-hero-img-bottom">
                            <div>
                                <p class="lp-hero-img-bottom-title">Lingkungan Akademik Unggul</p>
                                <p class="lp-hero-img-bottom-sub">Kampus Ciseureuh, Purwakarta â€” Berakhlak &amp; Berprestasi</p>
                            </div>
                            <div class="lp-hero-img-star">
                                <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-yellow);">stars</span>
                            </div>
                        </div>
                    </div>
                    {{-- Stats --}}
                    <div class="lp-hero-stats">
                        <div class="lp-hero-stat" style="background:var(--lp-primary-subtle);border-color:rgba(0,90,180,0.1);">
                            <span class="lp-hero-stat-val" style="color:var(--lp-primary);">100%</span>
                            <span class="lp-hero-stat-label">Lulusan Unggul</span>
                        </div>
                        <div class="lp-hero-stat" style="background:rgba(254,249,195,0.6);border-color:rgba(234,179,8,0.3);">
                            <span class="lp-hero-stat-val" style="color:var(--lp-yellow-dark);">4 Track</span>
                            <span class="lp-hero-stat-label">Kelas Pilihan</span>
                        </div>
                        <div class="lp-hero-stat" style="background:rgba(220,252,231,0.7);border-color:rgba(22,163,74,0.2);">
                            <span class="lp-hero-stat-val" style="color:var(--lp-green);">15+</span>
                            <span class="lp-hero-stat-label">Ekstrakurikuler</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION 2: INFORMASI SINGKAT PPDB (3 Cards) --}}
    {{-- ============================================================ --}}
    <section class="lp-info-section" id="informasi">
        <div class="lp-section">
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;">
                <span class="lp-section-tag">Akses Cepat PPDB</span>
                <h2 class="lp-section-title">Panduan Pokok Pendaftaran PPDB</h2>
                <p class="lp-section-desc">Akses cepat ketentuan berkas digital, peminatan program studi, dan transparansi rincian biaya pendidikan.</p>
            </div>

            <div class="lp-info-grid">
                {{-- Card 1: Persyaratan Softfile (Blue) --}}
                <div class="lp-info-card" style="--hover-border:rgba(0,90,180,0.4);">
                    <div>
                        <div class="lp-info-card-icon" style="background:var(--lp-primary);color:var(--lp-on-primary);">
                            <span class="material-symbols-outlined" style="font-size:26px;">cloud_upload</span>
                        </div>
                        <span class="lp-info-card-tag" style="background:rgba(214,227,255,0.5);color:var(--lp-primary);">100% Soft File</span>
                        <h3>Persyaratan Berkas Digital</h3>
                        <p>Cukup siapkan file scan atau foto jelas dokumen resmi (KK, Akta Lahir, NISN) untuk diunggah langsung via portal.</p>
                    </div>
                    <div class="lp-info-card-footer">
                        <a class="lp-info-card-link" href="#persyaratan" style="color:var(--lp-primary);">
                            <span>Lihat Detail Softfile</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>

                {{-- Card 2: Program (Yellow) --}}
                <div class="lp-info-card">
                    <div>
                        <div class="lp-info-card-icon" style="background:var(--lp-yellow);color:#451a03;">
                            <span class="material-symbols-outlined" style="font-size:26px;">category</span>
                        </div>
                        <span class="lp-info-card-tag" style="background:var(--lp-yellow-light);color:var(--lp-yellow-dark);">Peminatan Bakat</span>
                        <h3>4 Pilihan Program</h3>
                        <p>Pilihan kelas Reguler, Bahasa (Oxford TeachCast), Tahfizh Al-Qur'an, dan Kelas ICT / Komputer Terapan.</p>
                    </div>
                    <div class="lp-info-card-footer">
                        <a class="lp-info-card-link" href="#program" style="color:var(--lp-yellow-dark);">
                            <span>Lihat Detail Program</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>

                {{-- Card 3: Biaya (Green) --}}
                <div class="lp-info-card">
                    <div>
                        <div class="lp-info-card-icon" style="background:var(--lp-green);color:var(--lp-on-primary);">
                            <span class="material-symbols-outlined" style="font-size:26px;">payments</span>
                        </div>
                        <span class="lp-info-card-tag" style="background:var(--lp-green-light);color:var(--lp-green);">Transparansi Finansial</span>
                        <h3>Rincian Biaya Transparan</h3>
                        <p>Rincian tabel biaya resmi transparan untuk santri Putra dan Putri dengan diskon khusus bagi alumni &amp; bersaudara.</p>
                    </div>
                    <div class="lp-info-card-footer">
                        <a class="lp-info-card-link" href="#biaya" style="color:var(--lp-green);">
                            <span>Lihat Simulasi Biaya</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 3: PERSYARATAN SOFTFILE --}}
    {{-- ============================================================ --}}
    <section class="lp-softfile-section" id="persyaratan">
        <div class="lp-section">
            {{-- Banner --}}
            <div class="lp-softfile-banner">
                <div style="display:flex;align-items:flex-start;gap:1rem;">
                    <div class="lp-softfile-banner-icon">
                        <span class="material-symbols-outlined" style="font-size:30px;">cloud_done</span>
                    </div>
                    <div>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:0.5rem;margin-bottom:4px;">
                            <span style="padding:2px 10px;border-radius:9999px;background:var(--lp-yellow);color:#451a03;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.04em;">PENTING &amp; PRAKTIS</span>
                            <span style="padding:2px 10px;border-radius:9999px;background:var(--lp-green-light);color:var(--lp-green);font-size:12px;font-weight:700;">Bebas Ribet</span>
                        </div>
                        <h3 class="lp-font-headline" style="font-size:22px;font-weight:900;color:#fff;margin:0;">100% Berkas Digital (Soft File)</h3>
                        <p style="font-size:14px;color:rgba(191,219,254,1);margin-top:4px;max-width:600px;">
                            <strong>Tidak perlu membawa berkas fisik atau fotokopi ke sekolah saat pendaftaran awal.</strong> Cukup unggah file scan atau foto jelas berkas asli melalui formulir pendaftaran online.
                        </p>
                    </div>
                </div>
                <a class="lp-btn-secondary" href="{{ route('register') }}" style="flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:18px;">upload</span>
                    <span>Upload Berkas</span>
                </a>
            </div>

            {{-- Requirements Card --}}
            <div class="lp-softfile-card">
                <div class="lp-softfile-grid">
                    {{-- Left: Title --}}
                    <div style="display:flex;flex-direction:column;gap:1rem;">
                        <span class="lp-section-tag" style="width:fit-content;">
                            <span class="material-symbols-outlined" style="font-size:18px;">checklist</span>
                            Kelengkapan Berkas Digital
                        </span>
                        <h2 class="lp-font-headline" style="font-size:30px;font-weight:800;color:var(--lp-on-surface);margin:0;">Persyaratan Unggah Softfile</h2>
                        <p style="font-size:16px;color:var(--lp-on-surface-variant);line-height:1.6;">
                            Calon santri baru hanya perlu menyiapkan dokumen-dokumen utama berikut dalam bentuk digital. Pastikan teks terbaca jelas, tidak terpotong, dan tidak buram saat difoto atau discan.
                        </p>
                        <div class="lp-softfile-format-box">
                            <div style="display:flex;align-items:center;gap:8px;color:var(--lp-primary);font-weight:700;font-size:14px;">
                                <span class="material-symbols-outlined" style="font-size:20px;">info</span>
                                <span>Format File yang Diterima</span>
                            </div>
                            <div class="lp-softfile-format-tags">
                                <span class="lp-softfile-format-tag" style="color:var(--lp-primary);">PDF (Maks. 5 MB)</span>
                                <span class="lp-softfile-format-tag" style="color:var(--lp-green);">JPG / JPEG (Jelas)</span>
                                <span class="lp-softfile-format-tag" style="color:var(--lp-yellow-dark);">PNG (Jelas)</span>
                            </div>
                            <p style="font-size:12px;color:var(--lp-on-surface-variant);line-height:1.6;margin-top:8px;">
                                Dokumen asli cukup diperlihatkan nanti saat sesi wawancara dan observasi santri baru tanpa perlu menyerahkan fotokopi legalisir.
                            </p>
                        </div>
                    </div>

                    {{-- Right: Items --}}
                    <div class="lp-softfile-items">
                        <div class="lp-softfile-item">
                            <div class="lp-softfile-item-icon" style="background:var(--lp-primary);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:20px;">badge</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:16px;color:var(--lp-on-surface);">Scan / Foto Asli Kartu Keluarga (KK)</span>
                                    <span style="padding:2px 8px;border-radius:4px;background:var(--lp-primary-light);color:var(--lp-primary);font-size:11px;font-weight:700;">Wajib (Softfile)</span>
                                </div>
                                <p style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:2px;">File format PDF atau foto JPG/PNG yang jelas, memperlihatkan seluruh nomor KK dan data anggota keluarga ber-barcode aktif.</p>
                            </div>
                            <div class="lp-softfile-item-check"><span class="material-symbols-outlined" style="font-size:18px;">done</span></div>
                        </div>

                        <div class="lp-softfile-item">
                            <div class="lp-softfile-item-icon" style="background:var(--lp-green);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:20px;">description</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:16px;color:var(--lp-on-surface);">Scan / Foto Asli Akta Kelahiran</span>
                                    <span style="padding:2px 8px;border-radius:4px;background:var(--lp-green-light);color:var(--lp-green);font-size:11px;font-weight:700;">Wajib (Softfile)</span>
                                </div>
                                <p style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:2px;">File format PDF atau foto JPG/PNG asli untuk validasi tanggal lahir dan data kependudukan calon santri.</p>
                            </div>
                            <div class="lp-softfile-item-check"><span class="material-symbols-outlined" style="font-size:18px;">done</span></div>
                        </div>

                        <div class="lp-softfile-item">
                            <div class="lp-softfile-item-icon" style="background:var(--lp-yellow);color:#451a03;">
                                <span class="material-symbols-outlined" style="font-size:20px;">pin</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:16px;color:var(--lp-on-surface);">Tangkapan Layar / Bukti Cetak Digital NISN</span>
                                    <span style="padding:2px 8px;border-radius:4px;background:var(--lp-yellow-light);color:var(--lp-yellow-dark);font-size:11px;font-weight:700;">Wajib (Softfile)</span>
                                </div>
                                <p style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:2px;">Screenshot status aktif Nomor Induk Siswa Nasional dari laman resmi Kemendikbudristek (nisn.data.kemdikbud.go.id) atau surat keterangan sekolah.</p>
                            </div>
                            <div class="lp-softfile-item-check"><span class="material-symbols-outlined" style="font-size:18px;">done</span></div>
                        </div>

                        <div class="lp-softfile-item">
                            <div class="lp-softfile-item-icon" style="background:var(--lp-red);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:20px;">account_box</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:16px;color:var(--lp-on-surface);">Pas Foto Digital Calon Siswa (Terbaru)</span>
                                    <span style="padding:2px 8px;border-radius:4px;background:var(--lp-red-light);color:var(--lp-red);font-size:11px;font-weight:700;">Softfile Foto</span>
                                </div>
                                <p style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:2px;">File foto rapi (format JPG/PNG) ukuran 3x4 berlatar belakang merah atau biru, berpakaian sopan atau berseragam sekolah asal.</p>
                            </div>
                            <div class="lp-softfile-item-check"><span class="material-symbols-outlined" style="font-size:18px;">done</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 4: 4 PILIHAN PROGRAM UNGGULAN & YOUTUBE SHOWCASE   --}}
    {{-- ============================================================ --}}
    <section class="lp-program-section" id="program">
        <div class="lp-section">
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;max-width:820px;margin:0 auto 2.5rem;">
                <span class="lp-section-tag">Kurikulum Holistik Terpadu</span>
                <h2 class="lp-section-title">4 Pilihan Program Unggulan &amp; Pembiasaan Santri</h2>
                <p class="lp-section-desc">Pilih program peminatan untuk melihat rincian kurikulum spesifik, target capaian lulusan berjenjang kelas 7 hingga 9, serta program unggulan harian hingga tahunan.</p>
            </div>

            {{-- 1. 4 Interactive Program Selector Cards --}}
            <div class="lp-program-selector-grid">
                {{-- Selector Card 1: Tahfizh --}}
                <div class="lp-prog-select-card theme-tahfizh active" id="lp-prog-card-tahfizh" onclick="lpSelectProgram('tahfizh')">
                    <div>
                        <div class="lp-prog-card-top">
                            <div class="lp-prog-card-icon" style="background:var(--lp-green);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:26px;">menu_book</span>
                            </div>
                            <span class="lp-prog-card-badge" style="background:var(--lp-green-light);color:var(--lp-green);">QUR'ANI â€¢ 10 JUZ</span>
                        </div>
                        <h3>Kelas Tahfizh</h3>
                        <p>Fokus intensif hafalan Al-Qur'an tartil &amp; mutqin 10 juz, bimbingan tahsin bersanad, serta mutaba'ah halaqah berkala.</p>
                    </div>
                    <div class="lp-prog-card-bottom" style="color:var(--lp-green);">
                        <span>Target: Hafal 10 Juz</span>
                        <span class="lp-prog-active-indicator">
                            <span>Rincian</span>
                            <span class="material-symbols-outlined" style="font-size:16px;">arrow_downward</span>
                        </span>
                    </div>
                </div>

                {{-- Selector Card 2: ICT --}}
                <div class="lp-prog-select-card theme-ict" id="lp-prog-card-ict" onclick="lpSelectProgram('ict')">
                    <div>
                        <div class="lp-prog-card-top">
                            <div class="lp-prog-card-icon" style="background:var(--lp-primary);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:26px;">terminal</span>
                            </div>
                            <span class="lp-prog-card-badge" style="background:var(--lp-primary-light);color:var(--lp-primary);">DIGITAL â€¢ CODING</span>
                        </div>
                        <h3>Kelas ICT / Komputer</h3>
                        <p>Penguasaan teknologi informasi, Web Development, Robotika &amp; IoT, Multimedia digital, dan sertifikasi vokasi.</p>
                    </div>
                    <div class="lp-prog-card-bottom" style="color:var(--lp-primary);">
                        <span>Target: Web, IoT &amp; Multimedia</span>
                        <span class="lp-prog-active-indicator">
                            <span>Rincian</span>
                            <span class="material-symbols-outlined" style="font-size:16px;">arrow_downward</span>
                        </span>
                    </div>
                </div>

                {{-- Selector Card 3: Bahasa --}}
                <div class="lp-prog-select-card theme-bahasa" id="lp-prog-card-bahasa" onclick="lpSelectProgram('bahasa')">
                    <div>
                        <div class="lp-prog-card-top">
                            <div class="lp-prog-card-icon" style="background:var(--lp-yellow);color:#451a03;">
                                <span class="material-symbols-outlined" style="font-size:26px;">translate</span>
                            </div>
                            <span class="lp-prog-card-badge" style="background:var(--lp-yellow-light);color:var(--lp-yellow-dark);">INTERNATIONAL</span>
                        </div>
                        <h3>Kelas Bahasa</h3>
                        <p>Penguatan aktif Bahasa Inggris &amp; Arab bekerja sama resmi dengan Oxford TeachCast bersama penutur asli (native speaker).</p>
                    </div>
                    <div class="lp-prog-card-bottom" style="color:var(--lp-yellow-dark);">
                        <span>Target: 10 Jaminan Mutu (QA)</span>
                        <span class="lp-prog-active-indicator">
                            <span>Rincian</span>
                            <span class="material-symbols-outlined" style="font-size:16px;">arrow_downward</span>
                        </span>
                    </div>
                </div>

                {{-- Selector Card 4: Reguler --}}
                <div class="lp-prog-select-card theme-reguler" id="lp-prog-card-reguler" onclick="lpSelectProgram('reguler')">
                    <div>
                        <div class="lp-prog-card-top">
                            <div class="lp-prog-card-icon" style="background:var(--lp-primary-dark);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:26px;">school</span>
                            </div>
                            <span class="lp-prog-card-badge" style="background:var(--lp-primary-light);color:var(--lp-primary);">TERFAVORIT</span>
                        </div>
                        <h3>Kelas Reguler</h3>
                        <p>Pembelajaran terpadu Kurikulum Merdeka nasional dengan penguatan kepesantrenan Al-Muhajirin dan pembinaan akhlak mulia.</p>
                    </div>
                    <div class="lp-prog-card-bottom" style="color:var(--lp-primary-dark);">
                        <span>Target: Akademik &amp; Karakter</span>
                        <span class="lp-prog-active-indicator">
                            <span>Rincian</span>
                            <span class="material-symbols-outlined" style="font-size:16px;">arrow_downward</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. Detailed Program Panels (Switchable) --}}
            <div class="lp-prog-detail-wrapper">

                {{-- PANEL A: KELAS TAHFIZH --}}
                <div class="lp-prog-panel active" id="lp-prog-panel-tahfizh">
                    <div class="lp-panel-header">
                        <div class="lp-panel-header-left">
                            <div class="lp-panel-avatar" style="background:var(--lp-green-light);color:var(--lp-green);">
                                <span class="material-symbols-outlined" style="font-size:32px;">menu_book</span>
                            </div>
                            <div class="lp-panel-title-area">
                                <h3>Program Unggulan Kelas Tahfizh Al-Qur'an</h3>
                                <p>Mencetak generasi penghafal Al-Qur'an yang mutqin, berakhlak mulia, tartil dalam membaca, dan istiqamah mengamalkan nilai-nilai Qur'ani dalam keseharian.</p>
                            </div>
                        </div>
                        <div class="lp-panel-stat-pills">
                            <span class="lp-panel-stat-pill" style="border-color:rgba(22,163,74,0.3);color:var(--lp-green);">
                                <span class="material-symbols-outlined" style="font-size:16px;">military_tech</span>
                                Target Hafalan: 10 Juz
                            </span>
                            <span class="lp-panel-stat-pill">
                                <span class="material-symbols-outlined" style="font-size:16px;">format_list_numbered</span>
                                16 Program Unggulan
                            </span>
                            <span class="lp-panel-stat-pill">
                                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                                3 Rutinitas Harian
                            </span>
                        </div>
                    </div>

                    {{-- Target Lulusan Tahfizh --}}
                    <div class="lp-milestone-section">
                        <div class="lp-section-subheading" style="color:var(--lp-green);">
                            <span class="material-symbols-outlined" style="font-size:18px;">flag</span>
                            Target Lulusan Berjenjang: Total 10 Juz
                        </div>
                        <div class="lp-milestone-grid">
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:var(--lp-green-light);color:var(--lp-green);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                                    KELAS 7 (TARGET 4 JUZ)
                                </span>
                                <h4>Fase Pondasi &amp; Pra-Tahfizh</h4>
                                <p>Pemantapan makhraj huruf, tajwid dasar, tahsin klasikal &amp; individu, serta penanaman ritme hafalan harian One Day One Page.</p>
                            </div>
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:var(--lp-green-light);color:var(--lp-green);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                                    KELAS 8 (TARGET 4 JUZ)
                                </span>
                                <h4>Fase Akselerasi &amp; Karantina</h4>
                                <p>Akselerasi penambahan ziyadah, pembiasaan tasmi' per juz, keikutsertaan Karantina Tahfizh, dan Fullday For Muraja'ah (F2M).</p>
                            </div>
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:rgba(22,163,74,0.15);color:var(--lp-green);border:1px solid rgba(22,163,74,0.25);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">workspace_premium</span>
                                    KELAS 9 (TARGET 2 JUZ)
                                </span>
                                <h4>Fase Mutqin &amp; Munaqosyah</h4>
                                <p>Genap mencapai target 10 Juz Mutqin, penyelesaian Ujian Tasmi' Akhir (UTA), Munaqosyah Terbuka, dan Quran Essay Project.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Feature Dual Grid: 16 Program & Kegiatan Harian --}}
                    <div class="lp-prog-grid-dual">
                        <div class="lp-prog-box">
                            <div class="lp-prog-box-header">
                                <span class="material-symbols-outlined" style="color:var(--lp-green);font-size:22px;">stars</span>
                                <h4>16 Program Unggulan Kelas Tahfizh</h4>
                            </div>
                            <div class="lp-chips-grid">
                                <div class="lp-chip-item"><span class="lp-chip-num">01</span><span>Pra Tahfizh</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">02</span><span>One Day One Page</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">03</span><span>Tahfizh Menuntun (TM)</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">04</span><span>Salam Pagi bersama Al Qur'an (SAPAQ)</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">05</span><span>Ujian Kenaikan Juz</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">06</span><span>Khataman Qur'an</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">07</span><span>Fullday For Muraja'ah (F2M)</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">08</span><span>Tasmi'</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">09</span><span>Seminar Motivasi</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">10</span><span>Karantina Tahfizh</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">11</span><span>Munaqosyah</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">12</span><span>Ujian Tasmi' Akhir (UTA)</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">13</span><span>Qur'an Essay Project</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">14</span><span>Quran Teacher Project</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">15</span><span>Tahfizh Go To School</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">16</span><span>Studi Banding</span></div>
                            </div>
                        </div>

                        <div class="lp-prog-box">
                            <div class="lp-prog-box-header">
                                <span class="material-symbols-outlined" style="color:var(--lp-green);font-size:22px;">hourglass_top</span>
                                <h4>Kegiatan Rutin Harian Santri</h4>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;display:flex;align-items:flex-start;gap:12px;">
                                    <div style="width:34px;height:34px;border-radius:10px;background:var(--lp-green-light);color:var(--lp-green);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0;">1</div>
                                    <div>
                                        <div style="font-weight:700;font-size:14px;color:var(--lp-on-surface);">Murajaâ€™ah Hafalan Bersama</div>
                                        <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:2px 0 0 0;line-height:1.45;">Membaca dan mengulang hafalan secara serempak bersama asatidz untuk mengunci ingatan ayat.</p>
                                    </div>
                                </div>
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;display:flex;align-items:flex-start;gap:12px;">
                                    <div style="width:34px;height:34px;border-radius:10px;background:var(--lp-green-light);color:var(--lp-green);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0;">2</div>
                                    <div>
                                        <div style="font-weight:700;font-size:14px;color:var(--lp-on-surface);">Tahsin Klasikal &amp; Individu</div>
                                        <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:2px 0 0 0;line-height:1.45;">Bimbingan tajwid teoritis dan talaqqi langsung satu per satu di hadapan musyrif bersanad.</p>
                                    </div>
                                </div>
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;display:flex;align-items:flex-start;gap:12px;">
                                    <div style="width:34px;height:34px;border-radius:10px;background:var(--lp-green-light);color:var(--lp-green);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0;">3</div>
                                    <div>
                                        <div style="font-weight:700;font-size:14px;color:var(--lp-on-surface);">Setoran Ziyadah</div>
                                        <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:2px 0 0 0;line-height:1.45;">Setoran penambahan ayat baru secara teratur untuk mencapai target juz per semester.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lp-panel-footer">
                        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--lp-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-green);">verified</span>
                            <span>Menerima santri putra dan putri dengan kuota kelas terbatas.</span>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                            <button type="button" class="lp-btn-panel-cta" onclick="lpGoToFee('tahfizh')" style="background:var(--lp-surface);border:1px solid var(--lp-surface-container);color:var(--lp-on-surface);">
                                <span class="material-symbols-outlined" style="font-size:18px;">payments</span>
                                <span>Lihat Rincian Biaya</span>
                            </button>
                            <a href="{{ route('register') }}" class="lp-btn-panel-cta" style="background:var(--lp-green);color:#fff;">
                                <span>Daftar Kelas Tahfizh</span>
                                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- PANEL B: KELAS ICT --}}
                <div class="lp-prog-panel" id="lp-prog-panel-ict">
                    <div class="lp-panel-header">
                        <div class="lp-panel-header-left">
                            <div class="lp-panel-avatar" style="background:var(--lp-primary-light);color:var(--lp-primary);">
                                <span class="material-symbols-outlined" style="font-size:32px;">terminal</span>
                            </div>
                            <div class="lp-panel-title-area">
                                <h3>Program Unggulan Kelas ICT (Information &amp; Communication Technology)</h3>
                                <p>Mempersiapkan santri unggul di era digital melalui pembelajaran komputasi terapan, logika rekayasa perangkat lunak, robotika cerdas, dan produksi multimedia.</p>
                            </div>
                        </div>
                        <div class="lp-panel-stat-pills">
                            <span class="lp-panel-stat-pill" style="border-color:rgba(0,90,180,0.3);color:var(--lp-primary);">
                                <span class="material-symbols-outlined" style="font-size:16px;">code</span>
                                3 Jenjang Kompetensi
                            </span>
                            <span class="lp-panel-stat-pill">
                                <span class="material-symbols-outlined" style="font-size:16px;">smart_toy</span>
                                Robotika &amp; IoT
                            </span>
                            <span class="lp-panel-stat-pill">
                                <span class="material-symbols-outlined" style="font-size:16px;">work</span>
                                Magang &amp; Sidang TA
                            </span>
                        </div>
                    </div>

                    {{-- Target Lulusan ICT --}}
                    <div class="lp-milestone-section">
                        <div class="lp-section-subheading" style="color:var(--lp-primary);">
                            <span class="material-symbols-outlined" style="font-size:18px;">flag</span>
                            Target Lulusan Berjenjang Tiap Tingkat Kelas
                        </div>
                        <div class="lp-milestone-grid">
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:var(--lp-primary-light);color:var(--lp-primary);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">web</span>
                                    KELAS 7
                                </span>
                                <h4>Web Development</h4>
                                <p>Pengenalan arsitektur web, struktur halaman HTML5, styling responsif CSS3, algoritma logika dasar, serta pembuatan portofolio website mandiri.</p>
                            </div>
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:var(--lp-primary-light);color:var(--lp-primary);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">precision_manufacturing</span>
                                    KELAS 8
                                </span>
                                <h4>Robotika &amp; IoT (Internet of Things)</h4>
                                <p>Praktikum elektronika dasar, mikrokontroler (Arduino/ESP), instalasi sensor pintar, perakitan otomasi perangkat cerdas, dan integrasi cloud IoT.</p>
                            </div>
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:rgba(0,90,180,0.15);color:var(--lp-primary);border:1px solid rgba(0,90,180,0.25);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">movie</span>
                                    KELAS 9
                                </span>
                                <h4>Multimedia &amp; Tugas Akhir</h4>
                                <p>Desain grafis digital, animasi &amp; video editing profesional, pengalaman kerja magang (internship), serta pengujian sidang proyek tugas akhir.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Feature Dual Grid: 10 Program & Output --}}
                    <div class="lp-prog-grid-dual">
                        <div class="lp-prog-box">
                            <div class="lp-prog-box-header">
                                <span class="material-symbols-outlined" style="color:var(--lp-primary);font-size:22px;">bolt</span>
                                <h4>10 Program Unggulan Kelas ICT</h4>
                            </div>
                            <div class="lp-chips-grid">
                                <div class="lp-chip-item"><span class="lp-chip-num">01</span><span>ICT Bootcamp</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">02</span><span>ICT Mega Bootcamp</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">03</span><span>ICT Olympiad</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">04</span><span>Kunjungan Industri</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">05</span><span>Uji Kompetensi</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">06</span><span>Presentasi Proyek</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">07</span><span>ICT Expo</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">08</span><span>Internship / Magang</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">09</span><span>Proyek Tugas Akhir</span></div>
                                <div class="lp-chip-item"><span class="lp-chip-num">10</span><span>Sidang Tugas Akhir</span></div>
                            </div>
                        </div>

                        <div class="lp-prog-box">
                            <div class="lp-prog-box-header">
                                <span class="material-symbols-outlined" style="color:var(--lp-primary);font-size:22px;">memory</span>
                                <h4>Fasilitas &amp; Portofolio Pembelajaran</h4>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;">
                                    <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:14px;color:var(--lp-primary);">
                                        <span class="material-symbols-outlined" style="font-size:18px;">dns</span>
                                        Laboratorium Komputer Ber-AC
                                    </div>
                                    <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:4px 0 0 0;line-height:1.45;">Dilengkapi perangkat komputer spesifikasi modern dan koneksi internet serat optik dedicated untuk praktikum coding lancar.</p>
                                </div>
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;">
                                    <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:14px;color:var(--lp-primary);">
                                        <span class="material-symbols-outlined" style="font-size:18px;">devices</span>
                                        Kit Robotika &amp; Modul Sensor Lengkap
                                    </div>
                                    <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:4px 0 0 0;line-height:1.45;">Tiap santri berkesempatan merakit langsung mikrokontroler, aktuator, sensor gas, suhu, dan robot beroda cerdas.</p>
                                </div>
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;">
                                    <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:14px;color:var(--lp-primary);">
                                        <span class="material-symbols-outlined" style="font-size:18px;">workspace_premium</span>
                                        Sertifikasi &amp; Gelar Pameran ICT Expo
                                    </div>
                                    <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:4px 0 0 0;line-height:1.45;">Karya proyek santri dipamerkan ke publik dan diuji kelayakannya sebelum wisuda kelulusan.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lp-panel-footer">
                        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--lp-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-primary);">verified</span>
                            <span>Santri dibimbing langsung oleh instruktur praktisi teknologi informasi profesional.</span>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                            <button type="button" class="lp-btn-panel-cta" onclick="lpGoToFee('ict')" style="background:var(--lp-surface);border:1px solid var(--lp-surface-container);color:var(--lp-on-surface);">
                                <span class="material-symbols-outlined" style="font-size:18px;">payments</span>
                                <span>Lihat Rincian Biaya</span>
                            </button>
                            <a href="{{ route('register') }}" class="lp-btn-panel-cta" style="background:var(--lp-primary);color:#fff;">
                                <span>Daftar Kelas ICT</span>
                                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- PANEL C: KELAS BAHASA --}}
                <div class="lp-prog-panel" id="lp-prog-panel-bahasa">
                    <div class="lp-panel-header">
                        <div class="lp-panel-header-left">
                            <div class="lp-panel-avatar" style="background:var(--lp-yellow-light);color:var(--lp-yellow-dark);">
                                <span class="material-symbols-outlined" style="font-size:32px;">translate</span>
                            </div>
                            <div class="lp-panel-title-area">
                                <h3>Program Unggulan Kelas Bahasa Internasional</h3>
                                <p>Membangun keterampilan komunikasi aktif Bahasa Inggris dan Bahasa Arab, membiasakan public speaking berbobot, serta bermitra langsung bersama Oxford TeachCast.</p>
                            </div>
                        </div>
                        <div class="lp-panel-stat-pills">
                            <span class="lp-panel-stat-pill" style="border-color:rgba(234,179,8,0.4);color:var(--lp-yellow-dark);">
                                <span class="material-symbols-outlined" style="font-size:16px;">school</span>
                                Oxford TeachCast
                            </span>
                            <span class="lp-panel-stat-pill">
                                <span class="material-symbols-outlined" style="font-size:16px;">record_voice_over</span>
                                10 Quality Assurance
                            </span>
                            <span class="lp-panel-stat-pill">
                                <span class="material-symbols-outlined" style="font-size:16px;">update</span>
                                5 Siklus Pembiasaan
                            </span>
                        </div>
                    </div>

                    {{-- 5 Siklus Pembiasaan Bahasa --}}
                    <div class="lp-milestone-section">
                        <div class="lp-section-subheading" style="color:var(--lp-yellow-dark);">
                            <span class="material-symbols-outlined" style="font-size:18px;">cycle</span>
                            Siklus Pembiasaan Bahasa (Dari Harian Hingga Tahunan)
                        </div>
                        <div class="lp-cycle-grid">
                            <div class="lp-cycle-row">
                                <div class="lp-cycle-header" style="color:#b45309;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">today</span>
                                    <span>Rutinitas Harian</span>
                                </div>
                                <div class="lp-cycle-items">
                                    <span class="lp-cycle-badge">TTP (Target Time Practice)</span>
                                    <span class="lp-cycle-badge">Daily Speaking</span>
                                    <span class="lp-cycle-badge">Daily Expression</span>
                                </div>
                            </div>
                            <div class="lp-cycle-row">
                                <div class="lp-cycle-header" style="color:#0284c7;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">date_range</span>
                                    <span>Agenda Mingguan</span>
                                </div>
                                <div class="lp-cycle-items">
                                    <span class="lp-cycle-badge">Peer Learning</span>
                                    <span class="lp-cycle-badge">Speech</span>
                                    <span class="lp-cycle-badge">Tuesday English Mission</span>
                                    <span class="lp-cycle-badge">TeachCast (Native Speaker)</span>
                                    <span class="lp-cycle-badge">Spion</span>
                                </div>
                            </div>
                            <div class="lp-cycle-row">
                                <div class="lp-cycle-header" style="color:#7c3aed;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">calendar_month</span>
                                    <span>Agenda Bulanan</span>
                                </div>
                                <div class="lp-cycle-items">
                                    <span class="lp-cycle-badge">OMG (Oh My Grammar / Language Games)</span>
                                    <span class="lp-cycle-badge">Grade Class Championship</span>
                                    <span class="lp-cycle-badge">English Project</span>
                                </div>
                            </div>
                            <div class="lp-cycle-row">
                                <div class="lp-cycle-header" style="color:#059669;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">event_repeat</span>
                                    <span>Agenda Semester</span>
                                </div>
                                <div class="lp-cycle-items">
                                    <span class="lp-cycle-badge">English Camp</span>
                                    <span class="lp-cycle-badge">Confidence Show</span>
                                    <span class="lp-cycle-badge">Tutoring</span>
                                    <span class="lp-cycle-badge">Final Examination</span>
                                    <span class="lp-cycle-badge">Speaking Practice</span>
                                </div>
                            </div>
                            <div class="lp-cycle-row">
                                <div class="lp-cycle-header" style="color:#dc2626;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">flight_takeoff</span>
                                    <span>Agenda Tahunan</span>
                                </div>
                                <div class="lp-cycle-items">
                                    <span class="lp-cycle-badge">Hangout 1</span>
                                    <span class="lp-cycle-badge">Hangout 2 (Ekskursi Lingkungan Penutur Asing)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 10 Poin Quality Assurance --}}
                    <div class="lp-milestone-section" style="margin-top:2.25rem;">
                        <div class="lp-section-subheading" style="color:var(--lp-yellow-dark);">
                            <span class="material-symbols-outlined" style="font-size:18px;">verified</span>
                            10 Standar Jaminan Mutu Lulusan (Quality Assurance)
                        </div>
                        <div class="lp-qa-grid">
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">1</div>
                                <div>
                                    <h5>Jago Diskusi</h5>
                                    <p>Mampu menyampaikan pendapat dan menanggapi ide orang lain secara santun &amp; logis.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">2</div>
                                <div>
                                    <h5>Public Speaking Semakin Bagus</h5>
                                    <p>Terampil berbicara dan melakukan presentasi percaya diri di depan publik.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">3</div>
                                <div>
                                    <h5>Jago Berkomunikasi</h5>
                                    <p>Mampu berkomunikasi dengan berbagai orang dalam situasi nyata sehari-hari.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">4</div>
                                <div>
                                    <h5>Andal Berorganisasi</h5>
                                    <p>Membangun kemampuan bekerja sama dalam tim, memimpin, dan bertanggung jawab.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">5</div>
                                <div>
                                    <h5>Percaya Diri</h5>
                                    <p>Lebih berani tampil, berbicara di panggung, dan menunjukkan kemampuan terbaik diri.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">6</div>
                                <div>
                                    <h5>Lebih Ekspresif &amp; Komunikatif</h5>
                                    <p>Mampu mengekspresikan gagasan dan berinteraksi sosial dengan lebih nyaman &amp; luwes.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">7</div>
                                <div>
                                    <h5>Terampil Berargumentasi</h5>
                                    <p>Mampu menyampaikan alasan, sanggahan, dan tanggapan secara analitis serta terstruktur.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">8</div>
                                <div>
                                    <h5>Jago Speech</h5>
                                    <p>Terampil menyampaikan pidato dengan struktur tepat, ekspresi kuat, dan intonasi fasih.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">9</div>
                                <div>
                                    <h5>Jago Storytelling</h5>
                                    <p>Mampu menceritakan narasi atau kisah dengan ekspresi, intonasi, dan alur yang memikat.</p>
                                </div>
                            </div>
                            <div class="lp-qa-card">
                                <div class="lp-qa-icon">10</div>
                                <div>
                                    <h5>Menguasai 6 Tenses Dasar</h5>
                                    <p>Memahami dan mahir mengaplikasikan enam tenses dasar dalam komunikasi lisan &amp; tulisan.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lp-panel-footer">
                        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--lp-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-yellow-dark);">language</span>
                            <span>Didukung kurikulum resmi Oxford TeachCast dengan pembelajaran langsung native speaker.</span>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                            <button type="button" class="lp-btn-panel-cta" onclick="lpGoToFee('bahasa')" style="background:var(--lp-surface);border:1px solid var(--lp-surface-container);color:var(--lp-on-surface);">
                                <span class="material-symbols-outlined" style="font-size:18px;">payments</span>
                                <span>Lihat Rincian Biaya</span>
                            </button>
                            <a href="{{ route('register') }}" class="lp-btn-panel-cta" style="background:var(--lp-yellow);color:#451a03;">
                                <span>Daftar Kelas Bahasa</span>
                                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- PANEL D: KELAS REGULER --}}
                <div class="lp-prog-panel" id="lp-prog-panel-reguler">
                    <div class="lp-panel-header">
                        <div class="lp-panel-header-left">
                            <div class="lp-panel-avatar" style="background:var(--lp-primary-light);color:var(--lp-primary-dark);">
                                <span class="material-symbols-outlined" style="font-size:32px;">school</span>
                            </div>
                            <div class="lp-panel-title-area">
                                <h3>Program Unggulan Kelas Reguler Terpadu</h3>
                                <p>Menyatukan kurikulum nasional merdeka yang unggul secara akademik dengan nilai-nilai kepesantrenan Al-Muhajirin untuk mencetak generasi berkarakter dan cerdas seimbang.</p>
                            </div>
                        </div>
                        <div class="lp-panel-stat-pills">
                            <span class="lp-panel-stat-pill" style="border-color:rgba(0,90,180,0.3);color:var(--lp-primary);">
                                <span class="material-symbols-outlined" style="font-size:16px;">verified</span>
                                Terakreditasi A (Unggul)
                            </span>
                            <span class="lp-panel-stat-pill">
                                <span class="material-symbols-outlined" style="font-size:16px;">psychology</span>
                                Kurikulum Merdeka P5
                            </span>
                            <span class="lp-panel-stat-pill">
                                <span class="material-symbols-outlined" style="font-size:16px;">sports_soccer</span>
                                15+ Ekstrakurikuler
                            </span>
                        </div>
                    </div>

                    {{-- Target Lulusan Reguler --}}
                    <div class="lp-milestone-section">
                        <div class="lp-section-subheading" style="color:var(--lp-primary-dark);">
                            <span class="material-symbols-outlined" style="font-size:18px;">flag</span>
                            Target Lulusan Berjenjang Tiap Tingkat Kelas
                        </div>
                        <div class="lp-milestone-grid">
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:var(--lp-primary-light);color:var(--lp-primary-dark);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                                    KELAS 7
                                </span>
                                <h4>Adaptasi &amp; Pondasi Karakter</h4>
                                <p>Penguatan literasi, numerasi dasar, adaptasi lingkungan baru, pembiasaan shalat fardhu berjamaah di masjid, dan pembinaan adab santri mulia.</p>
                            </div>
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:var(--lp-primary-light);color:var(--lp-primary-dark);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                                    KELAS 8
                                </span>
                                <h4>Eksplorasi Minat &amp; Riset P5</h4>
                                <p>Pendalaman materi saintifik, pelaksanaan Proyek Penguatan Profil Pelajar Pancasila (P5), dan keikutsertaan kompetisi sains maupun seni.</p>
                            </div>
                            <div class="lp-milestone-card">
                                <span class="lp-milestone-badge" style="background:rgba(0,67,140,0.15);color:var(--lp-primary-dark);border:1px solid rgba(0,67,140,0.25);">
                                    <span class="material-symbols-outlined" style="font-size:14px;">workspace_premium</span>
                                    KELAS 9
                                </span>
                                <h4>Ketuntasan Akademik &amp; Kelulusan</h4>
                                <p>Pemantapan Asesmen Standar Nasional, penyusunan portofolio prestasi, bimbingan peminatan lanjutan, dan kesiapan menuju SMA/SMK favorit.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Feature Dual Grid: Pilar & Pembiasaan --}}
                    <div class="lp-prog-grid-dual">
                        <div class="lp-prog-box">
                            <div class="lp-prog-box-header">
                                <span class="material-symbols-outlined" style="color:var(--lp-primary-dark);font-size:22px;">stars</span>
                                <h4>Keunggulan Kurikulum Merdeka Terpadu</h4>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;">
                                    <div style="font-weight:700;font-size:14px;color:var(--lp-primary-dark);">Pembelajaran Diferensiasi Saintifik</div>
                                    <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:4px 0 0 0;line-height:1.45;">Metode belajar interaktif berpusat pada siswa, menumbuhkan nalar kritis, rasa ingin tahu, dan keterampilan pemecahan masalah.</p>
                                </div>
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;">
                                    <div style="font-weight:700;font-size:14px;color:var(--lp-primary-dark);">Bimbingan Prestasi &amp; Olimpiade Sains (OSN)</div>
                                    <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:4px 0 0 0;line-height:1.45;">Klub pembinaan khusus bagi santri berpotensi di bidang Matematika, IPA, IPS, Bahasa, dan Karya Ilmiah Remaja.</p>
                                </div>
                            </div>
                        </div>

                        <div class="lp-prog-box">
                            <div class="lp-prog-box-header">
                                <span class="material-symbols-outlined" style="color:var(--lp-primary-dark);font-size:22px;">mosque</span>
                                <h4>Pembiasaan Ibadah &amp; Karakter Pesantren</h4>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;">
                                    <div style="font-weight:700;font-size:14px;color:var(--lp-primary-dark);">Rutinitas Ibadah Harian</div>
                                    <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:4px 0 0 0;line-height:1.45;">Shalat Dhuha harian, Shalat Fardhu berjamaah di masjid kampus, pembacaan doa Al-Ma'tsurat, dan halaqah pembinaan akhlak.</p>
                                </div>
                                <div style="background:#fff;border:1px solid var(--lp-surface-container);border-radius:14px;padding:14px;">
                                    <div style="font-weight:700;font-size:14px;color:var(--lp-primary-dark);">Pengembangan Minat Ekstrakurikuler</div>
                                    <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:4px 0 0 0;line-height:1.45;">Pramuka wajib, PMR, Futsal, Basket, Pencak Silat, Hadrah &amp; Marawis, Kaligrafi Islami, dan Jurnalistik Sekolah.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lp-panel-footer">
                        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--lp-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-primary-dark);">verified</span>
                            <span>Pilihan tepat untuk pendidikan menyeluruh yang mengutamakan prestasi dan akhlak.</span>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                            <button type="button" class="lp-btn-panel-cta" onclick="lpGoToFee('reguler')" style="background:var(--lp-surface);border:1px solid var(--lp-surface-container);color:var(--lp-on-surface);">
                                <span class="material-symbols-outlined" style="font-size:18px;">payments</span>
                                <span>Lihat Rincian Biaya</span>
                            </button>
                            <a href="{{ route('register') }}" class="lp-btn-panel-cta" style="background:var(--lp-primary);color:#fff;">
                                <span>Daftar Kelas Reguler</span>
                                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- 3. YOUTUBE VIDEO SHOWCASE CARD (DUMMY URL READY)          --}}
            {{-- ======================================================== --}}
            <div class="lp-yt-showcase-card">
                <div class="lp-yt-showcase-grid">
                    {{-- Left: Responsive YouTube Video Player --}}
                    <div class="lp-yt-player-wrap">
                        <div class="lp-yt-responsive-ratio">
                            <!-- ======================================================== -->
                            <!-- LINK EMBED YOUTUBE DUMMY (Silakan ganti URL di bawah ini) -->
                            <!-- Contoh: src="https://www.youtube.com/embed/KODE_VIDEO_ANDA?rel=0" -->
                            <!-- ======================================================== -->
                            <iframe 
                                id="lpYoutubePlayer"
                                src="https://www.youtube.com/embed/BaXN8zWCQLQ?rel=0&modestbranding=1" 
                                title="Video Profil dan Kegiatan SMPS 2 Al-Muhajirin Purwakarta" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                allowfullscreen
                                loading="lazy">
                            </iframe>
                        </div>
                        <div class="lp-yt-badge-floating">
                            <span class="lp-yt-dot-live"></span>
                            <span>Video Profil &amp; Dokumentasi Kegiatan</span>
                        </div>
                    </div>

                    {{-- Right: Video Info & Action Links --}}
                    <div class="lp-yt-info-pane">
                        <div class="lp-yt-channel-tag">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            <span>SALURAN YOUTUBE RESMI SEKOLAH</span>
                        </div>

                        <h3 class="lp-yt-title">Saksikan Suasana Belajar &amp; Keseharian Santri Kami</h3>
                        
                        <p class="lp-yt-desc">
                            Lihat secara nyata lingkungan kampus SMPS 2 Al-Muhajirin yang asri dan representatif, praktikum laboratorium komputer, halaqah tahfizh berstandar mutqin, kemeriahan English camp, hingga aktivitas ibadah berjamaah santri.
                        </p>

                        <div class="lp-yt-features-list">
                            <div class="lp-yt-feature-item">
                                <span class="material-symbols-outlined">smart_display</span>
                                <span>Company Profile &amp; Tur Fasilitas Terpadu Kampus</span>
                            </div>
                            <div class="lp-yt-feature-item">
                                <span class="material-symbols-outlined">workspace_premium</span>
                                <span>Dokumentasi ICT Bootcamp, Pentas Seni, &amp; Wisuda Tahfizh</span>
                            </div>
                            <div class="lp-yt-feature-item">
                                <span class="material-symbols-outlined">diversity_3</span>
                                <span>Aktivitas Keseharian, Pembiasaan Ibadah, &amp; Karakter Santri</span>
                            </div>
                        </div>

                        <div class="lp-yt-actions">
                            <!-- ======================================================== -->
                            <!-- LINK NONTON YOUTUBE DUMMY (Silakan ganti URL di bawah)    -->
                            <!-- ======================================================== -->
                            <a href="https://www.youtube.com/watch?v=BaXN8zWCQLQ" target="_blank" rel="noopener noreferrer" class="lp-yt-btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                <span>Tonton di YouTube</span>
                                <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span>
                            </a>

                            <!-- ======================================================== -->
                            <!-- LINK CHANNEL YOUTUBE RESMI SEKOLAH                      -->
                            <!-- ======================================================== -->
                            <a href="https://www.youtube.com/@SMPFulldayAlMuhajirin" target="_blank" rel="noopener noreferrer" class="lp-yt-btn-secondary">
                                <span class="material-symbols-outlined" style="font-size:20px;color:#fca5a5;">subscriptions</span>
                                <span>Channel @SMPFulldayAlMuhajirin</span>
                            </a>
                        </div>
                        
                        <p class="lp-yt-tip">
                            <span class="material-symbols-outlined" style="font-size:15px;color:var(--lp-yellow);">info</span>
                            <span>Putar video langsung pada jendela di sebelah kiri atau klik tombol untuk membuka di aplikasi YouTube.</span>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 5: RINCIAN BIAYA (INTERACTIVE TABS) --}}
    {{-- ============================================================ --}}
    <section class="lp-pricing-section" id="biaya">
        <div class="lp-section">
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;margin-bottom:2rem;">
                <span class="lp-section-tag">Transparansi Finansial</span>
                <h2 class="lp-section-title">Rincian Biaya PPDB TP 2027â€“2028</h2>
                <p class="lp-section-desc">Pilih tab peminatan untuk melihat tabel rincian biaya pendidikan secara lengkap antara santri Putra dan Putri.</p>
            </div>

            {{-- Tab Controls --}}
            <div class="lp-pricing-tabs">
                <div class="lp-pricing-tabs-inner">
                    <button class="lp-tab-btn active" id="lp-tab-reguler" onclick="lpSwitchTab('reguler')" type="button">Kelas Reguler</button>
                    <button class="lp-tab-btn" id="lp-tab-bahasa" onclick="lpSwitchTab('bahasa')" type="button">Kelas Bahasa</button>
                    <button class="lp-tab-btn" id="lp-tab-tahfizh" onclick="lpSwitchTab('tahfizh')" type="button">Kelas Tahfizh</button>
                    <button class="lp-tab-btn" id="lp-tab-ict" onclick="lpSwitchTab('ict')" type="button">Kelas ICT</button>
                </div>
            </div>

            {{-- Pricing Table Card --}}
            <div class="lp-pricing-card">
                <div class="lp-pricing-header">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="lp-pricing-header-icon">
                            <span class="material-symbols-outlined" style="font-size:22px;">account_balance_wallet</span>
                        </div>
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="font-size:16px;font-weight:700;color:var(--lp-on-surface);" id="lpActiveProgramName">Program: Kelas Reguler</span>
                                <span id="lpProgramBadge" style="padding:2px 8px;background:rgba(0,90,180,0.1);color:var(--lp-primary);font-weight:700;font-size:11px;border-radius:6px;">Kelas Reguler</span>
                            </div>
                            <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:0;" id="lpProgramDesc">Rincian resmi biaya masuk tahun ajaran baru TP 2027â€“2028</p>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="padding:4px 12px;background:var(--lp-surface-pure);color:var(--lp-primary);font-weight:700;font-size:12px;border-radius:9999px;border:1px solid rgba(0,90,180,0.2);">Putra &amp; Putri</span>
                        <span style="padding:4px 12px;background:var(--lp-yellow-light);color:var(--lp-yellow-dark);font-weight:700;font-size:12px;border-radius:9999px;border:1px solid rgba(234,179,8,0.3);">TP 2027/2028</span>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="lp-pricing-table">
                        <thead>
                            <tr>
                                <th style="text-align:center;width:56px;">No</th>
                                <th>Rincian Komponen Biaya</th>
                                <th style="text-align:right;width:176px;">Putra</th>
                                <th style="text-align:right;width:176px;">Putri</th>
                            </tr>
                        </thead>
                        <tbody id="lpFeeTableBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="material-symbols-outlined" style="font-size:22px;">price_check</span>
                                        <span style="font-size:14px;">TOTAL BIAYA PENDAFTARAN AWAL</span>
                                    </div>
                                </td>
                                <td style="text-align:right;font-size:16px;" id="lpTotalPutra">Rp10.945.000</td>
                                <td style="text-align:right;font-size:16px;" id="lpTotalPutri">Rp11.145.000</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Summary Cards --}}
                <div class="lp-pricing-summary">
                    <div class="lp-pricing-summary-card" style="background:var(--lp-primary-subtle);border-color:rgba(0,90,180,0.2);">
                        <div>
                            <span style="font-size:12px;font-weight:700;color:var(--lp-on-surface-variant);">Total Estimasi Santri Putra:</span>
                            <p class="lp-pricing-summary-val" style="color:var(--lp-primary);margin:0;" id="lpBadgePutra">Rp10.945.000</p>
                        </div>
                        <div class="lp-pricing-summary-icon" style="background:rgba(0,90,180,0.1);color:var(--lp-primary);">
                            <span class="material-symbols-outlined" style="font-size:26px;">male</span>
                        </div>
                    </div>
                    <div class="lp-pricing-summary-card" style="background:rgba(220,252,231,0.6);border-color:rgba(22,163,74,0.2);">
                        <div>
                            <span style="font-size:12px;font-weight:700;color:var(--lp-on-surface-variant);">Total Estimasi Santri Putri:</span>
                            <p class="lp-pricing-summary-val" style="color:var(--lp-green);margin:0;" id="lpBadgePutri">Rp11.145.000</p>
                        </div>
                        <div class="lp-pricing-summary-icon" style="background:rgba(22,163,74,0.1);color:var(--lp-green);">
                            <span class="material-symbols-outlined" style="font-size:26px;">female</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="lp-pricing-notes">
                <div style="display:flex;align-items:center;gap:8px;color:var(--lp-red);font-weight:700;font-size:16px;margin-bottom:1rem;">
                    <span class="material-symbols-outlined" style="font-size:22px;">notification_important</span>
                    <span>Catatan Penting, Diskon Khusus &amp; Skema Pembayaran</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:1rem;">
                    <div>
                        <div class="lp-pricing-note-item" style="background:var(--lp-red-subtle);border-color:rgba(220,38,38,0.2);">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-red);flex-shrink:0;margin-top:2px;">stars</span>
                            <div style="font-size:12px;color:var(--lp-on-surface-variant);">
                                <strong style="color:var(--lp-red);">1. Diskon Registrasi Alumni SD Plus Al-Muhajirin:</strong>
                                <p style="margin:2px 0 0;color:var(--lp-on-surface);">Potongan biaya registrasi alumni SD Plus Al-Muhajirin sebesar <strong style="color:var(--lp-red);">Rp1.200.000</strong>.</p>
                            </div>
                        </div>
                        <div class="lp-pricing-note-item" style="background:rgba(254,249,195,0.7);border-color:rgba(234,179,8,0.4);">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-yellow-dark);flex-shrink:0;margin-top:2px;">group</span>
                            <div style="font-size:12px;color:var(--lp-on-surface-variant);">
                                <strong style="color:var(--lp-yellow-dark);">2. Diskon Registrasi Kakak Beradik:</strong>
                                <p style="margin:2px 0 0;color:var(--lp-on-surface);">Potongan registrasi kakak beradik non alumni SD Plus Al-Muhajirin sebesar <strong style="color:var(--lp-yellow-dark);">Rp400.000</strong>.</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="lp-pricing-note-text">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-primary);flex-shrink:0;margin-top:2px;">info</span>
                            <span><strong>3. Biaya Tambahan:</strong> Belum termasuk biaya Ekstrakulikuler dan Buku Paket.</span>
                        </div>
                        <div class="lp-pricing-note-text">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-red);flex-shrink:0;margin-top:2px;">timer</span>
                            <span><strong>4. Batas Waktu Registrasi:</strong> Batas waktu registrasi <strong style="color:var(--lp-red);">3 bulan setelah pengumuman kelulusan</strong> (syarat dan ketentuan berlaku).</span>
                        </div>
                        <div class="lp-pricing-note-text">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-green);flex-shrink:0;margin-top:2px;">payments</span>
                            <span><strong>Sistem Pembayaran:</strong> Pembayaran dilakukan via rekening resmi Bank Mandiri atau Loket Tata Usaha Gedung SMPS 2 Al-Muhajirin.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 6: ALUR PENDAFTARAN --}}
    {{-- ============================================================ --}}
    <section class="lp-timeline-section" id="alur">
        <div class="lp-section">
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;">
                <span class="lp-section-tag">Tahapan Terstruktur</span>
                <h2 class="lp-section-title">Alur Pendaftaran Mudah &amp; Cepat</h2>
                <p class="lp-section-desc">Empat langkah sederhana menjadi santri berprestasi di SMP Full Day Al-Muhajirin Purwakarta.</p>
            </div>

            <div class="lp-timeline-grid">
                {{-- Step 1 --}}
                <div class="lp-timeline-card fade-in-entry">
                    <div class="lp-timeline-step" style="background:var(--lp-primary);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(0,90,180,0.25);">01</div>
                    <span class="lp-timeline-label" style="color:var(--lp-primary);">Tahap Pertama</span>
                    <h3>Daftar Akun Online</h3>
                    <p>Isi identitas diri awal calon santri dan buat akun portal PPDB secara mandiri melalui website.</p>
                </div>

                {{-- Step 2 --}}
                <div class="lp-timeline-card fade-in-entry" style="border-color:rgba(234,179,8,0.4);">
                    <div class="lp-timeline-step" style="background:var(--lp-yellow);color:#451a03;">02</div>
                    <span class="lp-timeline-label" style="color:var(--lp-yellow-dark);">Tahap Kedua</span>
                    <h3>Upload Softfile &amp; Program</h3>
                    <p>Unggah file scan/foto jelas KK, Akta, NISN, Pas Foto digital dan tentukan pilihan kelas peminatan secara online.</p>
                </div>

                {{-- Step 3 --}}
                <div class="lp-timeline-card fade-in-entry">
                    <div class="lp-timeline-step" style="background:var(--lp-red);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(220,38,38,0.25);">03</div>
                    <span class="lp-timeline-label" style="color:var(--lp-red);">Tahap Ketiga</span>
                    <h3>Verifikasi &amp; Asesmen</h3>
                    <p>Pemeriksaan softfile oleh panitia, dilanjutkan observasi minat bakat serta wawancara santri &amp; orang tua.</p>
                </div>

                {{-- Step 4 --}}
                <div class="lp-timeline-card fade-in-entry">
                    <div class="lp-timeline-step" style="background:var(--lp-green);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(22,163,74,0.25);">04</div>
                    <span class="lp-timeline-label" style="color:var(--lp-green);">Tahap Akhir</span>
                    <h3>Pengumuman &amp; Registrasi</h3>
                    <p>Hasil seleksi diumumkan via akun portal, dilanjutkan proses konfirmasi daftar ulang administrasi santri baru.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 7: CTA BANNER --}}
    {{-- ============================================================ --}}
    <section class="lp-cta-section">
        <div class="lp-section">
            <div class="lp-cta-banner">
                <div class="lp-cta-glow-1"></div>
                <div class="lp-cta-glow-2"></div>
                <div class="lp-cta-content">
                    <div class="lp-cta-icon">
                        <span class="material-symbols-outlined" style="font-size:30px;color:var(--lp-yellow);">school</span>
                    </div>
                    <div class="lp-cta-badge">KUOTA TERBATAS TIAP ROMBEL</div>
                    <h2 class="lp-cta-title">Siap Bergabung Bersama SMP Full Day Al-Muhajirin?</h2>
                    <p class="lp-cta-desc">Pendaftaran sangat mudah dengan sistem <strong>100% softfile dokumen</strong>. Amankan kursi calon santri sebelum kuota tiap kelas peminatan terpenuhi!</p>
                    <div class="lp-cta-buttons">
                        <a class="lp-btn-red" href="{{ route('register') }}">
                            <span class="material-symbols-outlined" style="font-size:20px;">how_to_reg</span>
                            <span>Daftar Sekarang Online</span>
                        </a>
                        <a class="lp-btn-cta-green" href="https://wa.me/6287821055283" target="_blank" rel="noopener">
                            <span class="material-symbols-outlined" style="font-size:20px;">chat</span>
                            <span>Konsultasi WhatsApp (0878-2105-5283)</span>
                        </a>
                    </div>
                    <p class="lp-cta-help">
                        <span class="material-symbols-outlined" style="font-size:16px;">contact_support</span>
                        <span>Butuh panduan teknis formulir? Hubungi juga panitia admisi: <strong>0878-2228-2012</strong></span>
                    </p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    // ===== Interactive Pricing Tab Logic (Sumber: Rincian Biaya 2728.xlsx) =====
    const regulerFeeItems = [
        { label: "Infak Pendidikan Per Bulan (SPP)", putra: "Rp700.000", putri: "Rp700.000" },
        { label: "Pengembangan Sarana Prasarana Pendidikan", putra: "Rp2.500.000", putri: "Rp2.500.000" },
        { label: "Infak Bangunan", putra: "Rp4.000.000", putri: "Rp4.000.000" },
        { label: "Kalender, Majalah, Buku Karya Siswa, Buku Hijroti, Raport, Buku Panduan Ibadah, Foto, Kartu Pelajar", putra: "Rp550.000", putri: "Rp550.000" },
        { label: "Milad Yayasan", putra: "Rp100.000", putri: "Rp100.000" },
        { label: "Ujian Selama Setahun", putra: "Rp800.000", putri: "Rp800.000" },
        { label: "Kegiatan Kesiswaan dan Asesmen", putra: "Rp550.000", putri: "Rp550.000" },
        { label: "Seragam Sekolah dan Atribut: Pramuka, Putih Biru, Rompi, Seragam Khas, Seragam Batik, Seragam Muslim, Kaos Olahraga, dan Kerudung (Khusus Putri)", putra: "Rp1.595.000", putri: "Rp1.795.000" },
        { label: "Infaq Masjid", putra: "Rp150.000", putri: "Rp150.000" }
    ];

    const takhosusFeeItems = [
        { label: "Infak Pendidikan Per Bulan (SPP)", putra: "Rp800.000", putri: "Rp800.000" },
        { label: "Pengembangan Sarana Prasarana Pendidikan", putra: "Rp2.500.000", putri: "Rp2.500.000" },
        { label: "Infak Bangunan", putra: "Rp4.000.000", putri: "Rp4.000.000" },
        { label: "Kalender, Majalah, Buku Karya Siswa, Buku Hijroti, Raport, Buku Panduan Ibadah, Foto, Kartu Pelajar", putra: "Rp550.000", putri: "Rp550.000" },
        { label: "Milad Yayasan", putra: "Rp100.000", putri: "Rp100.000" },
        { label: "Ujian Selama Setahun", putra: "Rp800.000", putri: "Rp800.000" },
        { label: "Kegiatan Kesiswaan dan Asesmen", putra: "Rp550.000", putri: "Rp550.000" },
        { label: "Seragam Sekolah dan Atribut: Pramuka, Putih Biru, Rompi, Seragam Khas, Seragam Batik, Seragam Muslim, Kaos Olahraga, dan Kerudung (Khusus Putri)", putra: "Rp1.595.000", putri: "Rp1.795.000" },
        { label: "Infaq Masjid", putra: "Rp150.000", putri: "Rp150.000" }
    ];

    const lpFeeData = {
        reguler: {
            name: "Program: Kelas Reguler",
            badge: "Kelas Reguler",
            desc: "Rincian resmi biaya masuk tahun ajaran baru TP 2027â€“2028",
            items: regulerFeeItems,
            totalPutra: "Rp10.945.000",
            totalPutri: "Rp11.145.000"
        },
        bahasa: {
            name: "Program: Kelas Bahasa",
            badge: "Kelas Takhosus",
            desc: "Rumpun Kelas Takhosus (Bahasa, ICT, Tahfidz) TP 2027â€“2028",
            items: takhosusFeeItems,
            totalPutra: "Rp11.045.000",
            totalPutri: "Rp11.245.000"
        },
        tahfizh: {
            name: "Program: Kelas Tahfizh",
            badge: "Kelas Takhosus",
            desc: "Rumpun Kelas Takhosus (Bahasa, ICT, Tahfidz) TP 2027â€“2028",
            items: takhosusFeeItems,
            totalPutra: "Rp11.045.000",
            totalPutri: "Rp11.245.000"
        },
        ict: {
            name: "Program: Kelas ICT",
            badge: "Kelas Takhosus",
            desc: "Rumpun Kelas Takhosus (Bahasa, ICT, Tahfidz) TP 2027â€“2028",
            items: takhosusFeeItems,
            totalPutra: "Rp11.045.000",
            totalPutri: "Rp11.245.000"
        }
    };

    function lpRenderTable(key) {
        const data = lpFeeData[key];
        if (!data) return;

        const tbody = document.getElementById('lpFeeTableBody');
        const progName = document.getElementById('lpActiveProgramName');
        const progBadge = document.getElementById('lpProgramBadge');
        const progDesc = document.getElementById('lpProgramDesc');
        const totalPutra = document.getElementById('lpTotalPutra');
        const totalPutri = document.getElementById('lpTotalPutri');
        const badgePutra = document.getElementById('lpBadgePutra');
        const badgePutri = document.getElementById('lpBadgePutri');

        if (progName) progName.innerText = data.name;
        if (progBadge) {
            progBadge.innerText = data.badge;
            if (data.badge === 'Kelas Reguler') {
                progBadge.style.background = 'rgba(0,90,180,0.1)';
                progBadge.style.color = 'var(--lp-primary)';
            } else {
                progBadge.style.background = 'rgba(13,148,136,0.12)';
                progBadge.style.color = '#0f766e';
            }
        }
        if (progDesc) progDesc.innerText = data.desc;
        if (totalPutra) totalPutra.innerText = data.totalPutra;
        if (totalPutri) totalPutri.innerText = data.totalPutri;
        if (badgePutra) badgePutra.innerText = data.totalPutra;
        if (badgePutri) badgePutri.innerText = data.totalPutri;

        if (tbody) {
            let html = '';
            data.items.forEach(function(item, i) {
                const bg = i % 2 === 0 ? '' : 'background:var(--lp-surface-subtle);';
                html += '<tr style="' + bg + '">' +
                    '<td style="text-align:center;font-weight:500;color:var(--lp-on-surface-variant);">' + (i + 1) + '</td>' +
                    '<td style="font-weight:500;">' + item.label + '</td>' +
                    '<td style="text-align:right;font-weight:600;">' + item.putra + '</td>' +
                    '<td style="text-align:right;font-weight:600;">' + item.putri + '</td>' +
                    '</tr>';
            });
            tbody.innerHTML = html;
        }
    }

    function lpSwitchTab(selected) {
        var tabs = ['reguler', 'bahasa', 'tahfizh', 'ict'];
        tabs.forEach(function(tab) {
            var btn = document.getElementById('lp-tab-' + tab);
            if (!btn) return;
            if (tab === selected) {
                btn.className = 'lp-tab-btn active';
            } else {
                btn.className = 'lp-tab-btn';
            }
        });
        lpRenderTable(selected);
    }

    // ===== Interactive Program Deep-Dive Selector =====
    function lpSelectProgram(progKey) {
        var progs = ['tahfizh', 'ict', 'bahasa', 'reguler'];
        progs.forEach(function(key) {
            var card = document.getElementById('lp-prog-card-' + key);
            var panel = document.getElementById('lp-prog-panel-' + key);
            if (card) {
                if (key === progKey) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            }
            if (panel) {
                if (key === progKey) {
                    panel.style.display = 'block';
                    panel.classList.add('active');
                } else {
                    panel.style.display = 'none';
                    panel.classList.remove('active');
                }
            }
        });
    }

    // ===== Quick Link to Pricing Section with Matching Program Tab =====
    function lpGoToFee(tabKey) {
        lpSwitchTab(tabKey);
        var feeSection = document.getElementById('biaya');
        if (feeSection) {
            feeSection.scrollIntoView({ behavior: 'smooth' });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        lpRenderTable('reguler');
        lpSelectProgram('tahfizh');
    });
</script>
@endpush

