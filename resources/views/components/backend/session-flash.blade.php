@if(Session::has('success'))
<div class="alert alert-light-success alert-dismissible fade show" role="alert">
    {{ Session::get('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @php
        Session::forget('success');
    @endphp
</div>
@endif

@if(Session::has('fail'))
<div class="alert alert-light-danger alert-dismissible fade show">
    {{ Session::get('fail') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @php
        Session::forget('fail');
    @endphp
</div>
@endif

