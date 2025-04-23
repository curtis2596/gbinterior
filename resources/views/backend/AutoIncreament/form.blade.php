@extends($layout)

@section('content')

<?php

use App\Models\AutoIncreament;

$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];
?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <div class="row">
        <div class="offset-lg-3 col-lg-6">
            <div class="form-group mb-3">
                <?php $types = AutoIncreament::TYPE_LIST; ?>
                <x-Inputs.drop-down name="type" label="Type" :value="$model->type" :list="$types" class="form-control select2" :mandatory="true"/>
            </div>
            <div class="form-group mb-3">
                <x-Inputs.text-field name="pattern" label="Pattern" :value="$model->pattern" :mandatory="true"/>
                <div class="text-help">For Alphabet Full Month : MMM : (e.g. January, February)</div>
                <div class="text-help">For Alphabet Short Month : MM : (e.g. Jan, Feb)</div>
                <div class="text-help">For Numerical Month : M : (e.g. 01, 02)</div>
                <div class="text-help">For Full Year : YY : (e.g. 2024, 2025)</div>
                <div class="text-help">For Short Year : Y : (e.g. 24, 25)</div>
                <div class="text-help">For Counter : counter (in lower Case) (required)</div>
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

@endsection