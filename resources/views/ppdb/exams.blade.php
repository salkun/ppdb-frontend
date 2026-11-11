@extends('layouts.app')

@section('title', 'Ujian Seleksi')

@section('content')
<div class="db-content">
    <div class="db-profile-card mb-4">
        <h2 class="h4 fw-bold mb-2">Ujian Seleksi Calon Siswa</h2>
        <p class="mb-0 text-secondary">Tes ditentukan otomatis berdasarkan jurusan. Jawaban tersimpan saat Anda memilih jawaban. Timer tetap berjalan jika koneksi terputus.</p>
    </div>
    <div class="alert alert-warning">
        Jika Anda meninggalkan halaman ujian, sesi akan ditangguhkan. Setelah lebih dari tiga pelanggaran, ujian dihentikan oleh sistem. Deteksi browser memiliki keterbatasan dan tidak dapat mencegah semua cara keluar dari situs.
    </div>

    @if(session('success') || session('warning') || session('info'))
        <div class="alert {{ session('success') ? 'alert-success' : (session('warning') ? 'alert-warning' : 'alert-info') }}">
            {{ session('success') ?? session('warning') ?? session('info') }}
        </div>
    @endif

    @if(!$eligible)
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <h5 class="fw-bold">Selesaikan persyaratan untuk membuka ujian</h5>
                <p class="text-muted">Ujian terbuka setelah formulir lengkap, seluruh berkas wajib diunggah, dan pembayaran terverifikasi.</p>
                <ul class="mb-0">@foreach($missingRequirements as $item)<li>{{ $item }}</li>@endforeach</ul>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary mt-3">Kembali ke Dashboard PPDB</a>
            </div>
        </div>
    @else
        <div class="row g-3">
            @forelse($exams as $exam)
                @php
                    $attempt = $exam->attempt;
                    $statusLabels = [
                        'in_progress' => 'Sedang berlangsung',
                        'suspended' => 'Ditangguhkan',
                        'submitted' => 'Selesai',
                        'terminated' => 'Dihentikan sistem',
                    ];
                    $isOpen = $exam->published
                        && $exam->questions_count > 0
                        && $exam->starts_at
                        && $exam->ends_at
                        && now()->gte($exam->starts_at)
                        && now()->lte($exam->ends_at);
                @endphp
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-3">
                        <div class="card-body">
                            <h4 class="fw-bold">{{ $exam->title }}</h4>
                            <p class="text-muted">{{ $exam->instructions ?: 'Ikuti petunjuk pengawas sebelum memulai ujian.' }}</p>
                            <div class="small text-secondary mb-3">
                                {{ $exam->questions_count }} soal · {{ $exam->duration_minutes }} menit
                                @if($exam->starts_at) · Mulai {{ $exam->starts_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }} @endif
                                @if($exam->ends_at) · Selesai {{ $exam->ends_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }} @endif
                            </div>
                            @if($attempt)
                                @if(in_array($attempt->status, ['in_progress', 'suspended']))
                                    <a class="btn btn-primary" href="{{ route('exams.take', $exam) }}">{{ $attempt->status === 'suspended' ? 'Lanjutkan Ujian' : 'Masuk Kembali ke Ujian' }}</a>
                                @else
                                    <span class="badge bg-secondary">{{ $statusLabels[$attempt->status] ?? 'Status tidak diketahui' }}</span>
                                    @if($exam->results_released && $attempt->score !== null)
                                        <span class="ms-2 fw-semibold">Nilai: {{ number_format((float)$attempt->score, 2) }}</span>
                                    @else
                                        <span class="ms-2 text-muted small">Hasil menunggu pengumuman panitia.</span>
                                    @endif
                                @endif
                            @elseif($isOpen)
                                <form method="POST" action="{{ route('exams.start', $exam) }}" onsubmit="return confirm('Ujian hanya dapat diikuti satu kali. Timer terus berjalan setelah dimulai. Meninggalkan halaman akan menangguhkan ujian; pelanggaran keempat menghentikan ujian. Mulai sekarang?');">
                                    @csrf
                                    <button class="btn btn-primary" type="submit">Mulai Ujian</button>
                                </form>
                            @else
                                <button class="btn btn-secondary" type="button" disabled>{{ $exam->published ? 'Di luar jadwal ujian' : 'Ujian belum dibuka' }}</button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">Belum ada ujian yang dipublikasikan untuk jurusan Anda.</div></div>
            @endforelse
        </div>
    @endif
</div>
@endsection
