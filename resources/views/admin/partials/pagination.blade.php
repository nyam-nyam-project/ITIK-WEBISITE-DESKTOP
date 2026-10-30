@if($paginator->total() > 0)
    <div class="pagination">
        <span class="muted small">Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data</span>
        @if($paginator->hasPages())
            @php
                $halamanPusat = min($paginator->lastPage(), $paginator->currentPage());
                $awal = max(1, $halamanPusat - 2);
                $akhir = min($paginator->lastPage(), $halamanPusat + 2);
            @endphp
            <nav class="page-links" aria-label="Navigasi halaman">
                @if($paginator->onFirstPage())<span class="page-link" aria-disabled="true">Sebelumnya</span>@else<a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>@endif
                @if($awal > 1)<a class="page-link" href="{{ $paginator->url(1) }}" aria-label="Halaman 1">1</a>@if($awal > 2)<span aria-hidden="true">…</span>@endif @endif
                @foreach($paginator->getUrlRange($awal, $akhir) as $halaman => $url)
                    @if($halaman === $paginator->currentPage())<span class="page-link" aria-current="page" aria-label="Halaman {{ $halaman }}">{{ $halaman }}</span>@else<a class="page-link" href="{{ $url }}" aria-label="Halaman {{ $halaman }}">{{ $halaman }}</a>@endif
                @endforeach
                @if($akhir < $paginator->lastPage())@if($akhir < $paginator->lastPage() - 1)<span aria-hidden="true">…</span>@endif<a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Halaman {{ $paginator->lastPage() }}">{{ $paginator->lastPage() }}</a>@endif
                @if($paginator->hasMorePages())<a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>@else<span class="page-link" aria-disabled="true">Berikutnya</span>@endif
            </nav>
        @endif
    </div>
@endif
