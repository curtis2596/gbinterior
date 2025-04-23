@extends($layout)

@section('content')

<div class="my-2 pt-2">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <?php
                    $ajax_view_name = 'errors.ajax.' . $error_code;

                    if (!view()->exists($ajax_view_name))
                    {
                        $ajax_view_name = 'errors.ajax.default';
                    }
                ?>
                @include($ajax_view_name)
            </div>
        </div>
    </div>
</div>

@endsection