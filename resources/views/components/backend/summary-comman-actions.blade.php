<ul class="action">
    
    @if(in_array("show", $buttonList))
    <li>
        <a class="btn btn-outline-info summary-action-button" href="{{ route($routePrefix . '.show',[$id]) }}">
            <i class="icon-pencil-alt"></i>
        </a>
    </li>
    @endif

    @if(in_array("edit", $buttonList))
    <li>
        <a class="btn btn-outline-primary summary-action-button" href="{{ route($routePrefix . '.edit',[$id]) }}">
            <i class="icon-pencil-alt"></i>
        </a>
    </li>
    @endif

    @if(in_array("delete", $buttonList))
    <li>
        <x-Backend.summary-delete-button url="{{ route($routePrefix . '.destroy', [$id]) }}" />
    </li>
    @endif

    @if(in_array("pdf", $buttonList))
    <li>
        <a class="btn btn-outline-info summary-action-button" href="{{ route($routePrefix . '.pdf', [$id]) }}" >
            <i class="fa-solid fa-file-pdf"></i>
        </a>
    </li>
    @endif

    @if(in_array("print", $buttonList))
    <li>
        <a class="btn btn-outline-info summary-action-button" href="{{ route($routePrefix . '.print', [$id]) }}" >
            <i class="fa-solid fa-print"></i> Print
        </a>
    </li>
    @endif
</ul>