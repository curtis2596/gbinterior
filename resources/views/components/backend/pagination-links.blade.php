@if(isset($withInfo) && $withInfo)
    {{ $records->appends(request()->except('page'))->links('pagination::default-with-info') }}
@else
    {{ $records->appends(request()->except('page'))->links('pagination::default') }}
@endif