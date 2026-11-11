@extends('admin.layout')

@section('title', 'Pengumuman Calon Siswa')
@section('header_title', 'Pengumuman Calon Siswa')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-4">
                    <span class="material-symbols-outlined text-primary" style="font-size:30px;">campaign</span>
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Pengumuman Dashboard Siswa</h5>
                        <p class="text-muted small mb-0">Pengumuman aktif akan tampil di bagian atas dashboard calon siswa dan muncul sebagai pop-up setelah mereka login.</p>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('admin.ppdb.announcement.save') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="enabled" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="announcementEnabled" name="enabled" value="1" @checked(old('enabled', $announcement['enabled']))>
                        <label class="form-check-label fw-semibold" for="announcementEnabled">Aktifkan pengumuman untuk calon siswa</label>
                    </div>

                    <div class="mb-3">
                        <label for="announcementTitle" class="form-label fw-semibold">Judul pengumuman</label>
                        <input type="text" class="form-control" id="announcementTitle" name="title" maxlength="150" value="{{ old('title', $announcement['title']) }}" placeholder="Contoh: Jadwal wawancara calon siswa">
                    </div>

                    <div class="mb-4">
                        <label for="announcementMessage" class="form-label fw-semibold">Isi pengumuman</label>
                        <textarea class="form-control" id="announcementMessage" name="message" rows="7" maxlength="5000" placeholder="Tuliskan informasi yang perlu diketahui calon siswa...">{{ old('message', $announcement['message']) }}</textarea>
                        <div class="form-text">Baris baru akan dipertahankan saat pengumuman ditampilkan.</div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-semibold d-inline-flex align-items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                        Simpan Pengumuman
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
