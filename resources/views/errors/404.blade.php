@extends($layout)

@section('content')

<div class="my-2 pt-2">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                @include('errors.ajax.404')
                <div class="text-center">
                    <div class="help-links">
                        <a href="/home" class="btn btn-light">Goto Home</a>
                        <span class="btn btn-light" onclick="history.back();">Back</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection