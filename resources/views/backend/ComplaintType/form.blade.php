@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];

?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <div class="row">
        <div class="offset-lg-3 col-lg-6">            
            <div class="form-group mb-3">
                <x-Inputs.text-field name="name" label="Complaint Name *" placeholder="Enter Complaint Name" :value="$model->name" />
            </div>

            <div class="form-group mb-3">
                <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active" />
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

@endsection