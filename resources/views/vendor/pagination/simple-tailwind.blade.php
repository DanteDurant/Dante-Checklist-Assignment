@if ($paginator->hasPages())
    @php
        $btn = 'relative inline-flex items-center px-4 py-2 text-sm font-medium leading-5 rounded-md border border-ui-border transition shadow-sm shadow-black/[0.03] dark:shadow-black/25';
        $link = $btn.' bg-ui-surface text-ui-fg-muted hover:bg-ui-muted hover:text-ui-fg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas';
        $disabled = $btn.' bg-ui-muted text-ui-fg-subtle cursor-default';
    @endphp
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="{{ $disabled }}">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $link }}">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $link }}">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="{{ $disabled }}">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
