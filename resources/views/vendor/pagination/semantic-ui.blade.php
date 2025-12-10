@if ($paginator->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-outline-secondary disabled">
                <i class="bx bx-chevron-left"></i> Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-chevron-left"></i> Prev
            </a>
        @endif

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm btn-primary">
                Next <i class="bx bx-chevron-right"></i>
            </a>
        @else
            <span class="btn btn-sm btn-outline-secondary disabled">
                Next <i class="bx bx-chevron-right"></i>
            </span>
        @endif

    </div>
@endif
