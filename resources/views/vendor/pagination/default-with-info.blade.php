<div class="row">
    <div class="col-md-6 hidden-sm">
        <h6 class="card-title">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
            <span class="font-medium">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!}
            <span class="font-medium">{{ $paginator->lastItem() }}</span>
            @else
            {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </h6>
    </div>
    <div class="col-md-6">

        @if ($paginator->hasPages())
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-primary justify-content-end">
                @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <a class="page-link" href="javascript:void(0)">&lsaquo;</a>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ \Request::url() }}" rel="prev" >First</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
                @endif

                @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                <li class="page-link disabled" aria-disabled="true">
                    <a class="page-link" href="javascript:void(0)">{{ $element }}</a>
                </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <li class="page-item active" aria-current="page">
                        <a class="page-link" href="javascript:void(0)">{{ $page }}</a>
                    </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
                @endif
                @endforeach
                @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="{{ get_current_path_url(['page' => $paginator->lastPage() ]) }}" rel="last">Last</a>
                </li>
                @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <a class="page-link" href="javascript:void(0)">&rsaquo;</a>
                </li>
                @endif
            </ul>
        </nav>
        @endif
    </div>
</div>