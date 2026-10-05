@if(isset($paginator) && $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="card-footer bg-white border-top py-3 px-3 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <!-- Info & Per Page Selector -->
        <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
            <span>
                Menampilkan <strong class="text-dark">{{ $paginator->firstItem() ?? 0 }}</strong> - <strong class="text-dark">{{ $paginator->lastItem() ?? 0 }}</strong> dari <strong class="text-dark">{{ $paginator->total() }}</strong> data
            </span>
            <span class="d-none d-sm-inline text-muted">&bull;</span>
            <div class="d-inline-flex align-items-center gap-1">
                <label for="perPageSelect-{{ $paginator->getPageName() }}" class="text-secondary" style="font-size: 12px;">Baris:</label>
                <select id="perPageSelect-{{ $paginator->getPageName() }}" 
                        class="form-select form-select-sm py-0 ps-2 pe-4" 
                        style="width: auto; height: 30px; font-size: 12px; border-radius: 6px; cursor: pointer;"
                        onchange="window.location.href=this.value">
                    @foreach([10, 20, 30, 50, 100] as $size)
                        @php
                            $targetUrl = request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]);
                        @endphp
                        <option value="{{ $targetUrl }}" {{ (int)$paginator->perPage() === $size ? 'selected' : '' }}>
                            {{ $size }} / hal
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Pagination Controls -->
        @if($paginator->hasPages())
            <nav aria-label="Navigasi Halaman" class="ms-md-auto">
                <ul class="pagination pagination-sm mb-0 gap-1 flex-wrap justify-content-center">
                    <!-- Tombol Sebelumnya -->
                    <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2 px-2.5 py-1 d-flex align-items-center" 
                           href="{{ $paginator->previousPageUrl() ?: '#' }}" 
                           aria-label="Sebelumnya" 
                           title="Halaman Sebelumnya">
                            <span class="material-symbols-outlined" style="font-size: 16px;">chevron_left</span>
                            <span class="d-none d-sm-inline ms-1" style="font-size: 12px;">Prev</span>
                        </a>
                    </li>

                    @php
                        $currentPage = $paginator->currentPage();
                        $lastPage = $paginator->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    <!-- Halaman Pertama jika jauh -->
                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link rounded-2 px-2.5 py-1" style="font-size: 12px;" href="{{ $paginator->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled"><span class="page-link rounded-2 px-2 py-1" style="font-size: 12px;">...</span></li>
                        @endif
                    @endif

                    <!-- Range Halaman Sekitar Current Page -->
                    @for($page = $start; $page <= $end; $page++)
                        <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                            @if($page === $currentPage)
                                <span class="page-link rounded-2 px-2.5 py-1 fw-bold" style="font-size: 12px; background-color: #005ab4; border-color: #005ab4;">{{ $page }}</span>
                            @else
                                <a class="page-link rounded-2 px-2.5 py-1" style="font-size: 12px;" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endfor

                    <!-- Halaman Terakhir jika jauh -->
                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <li class="page-item disabled"><span class="page-link rounded-2 px-2 py-1" style="font-size: 12px;">...</span></li>
                        @endif
                        <li class="page-item">
                            <a class="page-link rounded-2 px-2.5 py-1" style="font-size: 12px;" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                        </li>
                    @endif

                    <!-- Tombol Berikutnya -->
                    <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2 px-2.5 py-1 d-flex align-items-center" 
                           href="{{ $paginator->nextPageUrl() ?: '#' }}" 
                           aria-label="Berikutnya" 
                           title="Halaman Berikutnya">
                            <span class="d-none d-sm-inline me-1" style="font-size: 12px;">Next</span>
                            <span class="material-symbols-outlined" style="font-size: 16px;">chevron_right</span>
                        </a>
                    </li>
                </ul>
            </nav>
        @endif
    </div>
</div>
@endif
