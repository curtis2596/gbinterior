@if(isset($isActive) && $isActive)
    <span class="badge bg-success">Active</span>
    <br/>
    <a class="de_activate" href="{{ route($routePrefix . '.de_activate', ['id' => $id]) }}">De-activate</a>
@else
    <span class="badge bg-danger">De-Active</span>
    <br/>
    <a class="activate" href="{{ route($routePrefix . '.activate', ['id' => $id]) }}">Activate</a>
@endif