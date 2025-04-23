@extends($layout)

@section('content')

<div class="my-2 pt-2">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 pt-3">
                @include('errors.ajax.406')
                <div class="text-center">
                    <span class="btn btn-secondary go_back">Go Back</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection