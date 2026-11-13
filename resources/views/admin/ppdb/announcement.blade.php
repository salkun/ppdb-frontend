@extends('admin.layout')

@section('title', 'Pengumuman')
@section('header_title', 'Pengumuman')

@php
    $sections = [
        'landing' => [
            'heading' => 'Pop-up Landing Page',
            'desc' => 'Muncul sebagai pop-up saat pengunjung pertama kali membuka website (sebelum login).',
            'icon' => 'public',
        ],
        'dashboard' => [
            'heading' => 'Pengumuman Dashboard Siswa',
            'desc' => 'Tampil di bagian atas dashboard calon siswa dan sebagai pop-up setelah mereka login.',
            'icon' => 'campaign',
        ],
    ];
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @foreach($sections as $scope => $meta)
            @php $data = $announcements[$scope]; $isActive = old('scope') === $scope; @endphp
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <span class="material-symbols-outlined text-primary" style="font-size:30px;">{{ $meta['icon'] }}</span>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">{{ $meta['heading'] }}</h5>
                            <p class="text-muted small mb-0">{{ $meta['desc'] }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.ppdb.announcement.save') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="scope" value="{{ $scope }}">

                        <div class="form-check form-switch mb-4">
                            <input type="hidden" name="enabled" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="enabled_{{ $scope }}" name="enabled" value="1" @checked($isActive ? old('enabled') : $data['enabled'])>
                            <label class="form-check-label fw-semibold" for="enabled_{{ $scope }}">Aktifkan pengumuman ini</label>
                        </div>

                        <div class="mb-3">
                            <label for="title_{{ $scope }}" class="form-label fw-semibold">Judul pengumuman</label>
                            <input type="text" class="form-control" id="title_{{ $scope }}" name="title" maxlength="150" value="{{ $isActive ? old('title') : $data['title'] }}" placeholder="Contoh: Pendaftaran gelombang 1 dibuka">
                        </div>

                        <div class="mb-4">
                            <label for="message_{{ $scope }}" class="form-label fw-semibold">Isi pengumuman</label>
                            <textarea class="form-control" id="message_{{ $scope }}" name="message" rows="6" maxlength="5000" placeholder="Tuliskan informasi yang perlu diketahui...">{{ $isActive ? old('message') : $data['message'] }}</textarea>
                            <div class="form-text">Baris baru akan dipertahankan saat pengumuman ditampilkan.</div>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold d-inline-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                            Simpan
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
