@if ($paginator->hasPages())
    @php
        $btn = 'relative inline-flex items-center px-4 py-2 text-sm font-medium leading-5 rounded-md transition border border-ui-border shadow-sm shadow-black/[0.03] dark:shadow-black/25';
        $btnLink = $btn.' bg-ui-surface text-ui-fg-muted hover:bg-ui-muted hover:text-ui-fg focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas active:bg-ui-elevated';
        $btnDisabled = $btn.' bg-ui-muted text-ui-fg-subtle cursor-default';
        $iconBtn = 'relative inline-flex items-center px-2 py-2 text-sm font-medium border border-ui-border bg-ui-surface text-ui-fg-muted';
        $iconLink = $iconBtn.' leading-5 hover:bg-ui-muted hover:text-ui-fg focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas active:bg-ui-elevated';
        $iconDisabled = $iconBtn.' cursor-default text-ui-fg-subtle bg-ui-muted';
        $pageMuted = 'relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium border border-ui-border bg-ui-muted text-ui-fg leading-5 cursor-default';
        $pageInactive = 'relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium border border-ui-border bg-ui-surface text-ui-fg-muted leading-5 hover:bg-ui-muted hover:text-ui-fg focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ui-ring focus-visible:ring-offset-2 focus-visible:ring-offset-ui-canvas active:bg-ui-elevated';
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="{{ $btnDisabled }}">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="{{ $btnLink }}">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="{{ $btnLink }} ml-3">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="{{ $btnDisabled }} ml-3">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm leading-5 text-ui-fg-muted">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-ui-fg">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-semibold text-ui-fg">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-semibold text-ui-fg">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rtl:flex-row-reverse rounded-md shadow-sm shadow-black/[0.04] dark:shadow-black/35">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="{{ $iconDisabled }} rounded-l-md cursor-default" aria-hidden="true">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $iconLink }} rounded-l-md" aria-label="{{ __('pagination.previous') }}">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="{{ $pageMuted }}">{{ $element }}</span>
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="{{ $pageMuted }} font-semibold text-ui-fg">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="{{ $pageInactive }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $iconLink }} -ml-px rounded-r-md" aria-label="{{ __('pagination.next') }}">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="{{ $iconDisabled }} -ml-px rounded-r-md cursor-default" aria-hidden="true">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
