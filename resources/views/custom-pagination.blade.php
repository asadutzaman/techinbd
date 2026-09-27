{{-- Page links for the shop (styles: .sf-pager in public/css/storefront.css). Phones get the arrows and "Page 2 of 5". --}}
@if ($paginator->hasPages())
    <nav class="sf-pager" aria-label="Pages">
        @if ($paginator->onFirstPage())
            <span class="sf-pager-step is-disabled" aria-hidden="true">
                <i class="fas fa-chevron-left"></i><span class="sf-pager-label">Previous</span>
            </span>
        @else
            <a class="sf-pager-step" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                <i class="fas fa-chevron-left" aria-hidden="true"></i><span class="sf-pager-label">Previous</span>
            </a>
        @endif

        <ol class="sf-pager-list">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="sf-pager-gap" aria-hidden="true">…</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="sf-pager-page" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="sf-pager-page" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ol>

        <span class="sf-pager-status">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="sf-pager-step" href="{{ $paginator->nextPageUrl() }}" rel="next">
                <span class="sf-pager-label">Next</span><i class="fas fa-chevron-right" aria-hidden="true"></i>
            </a>
        @else
            <span class="sf-pager-step is-disabled" aria-hidden="true">
                <span class="sf-pager-label">Next</span><i class="fas fa-chevron-right"></i>
            </span>
        @endif
    </nav>
@endif
