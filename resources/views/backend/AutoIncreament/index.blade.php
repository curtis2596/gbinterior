@extends($layout)

@section('content')

<?php

use App\Models\AutoIncreament;

    $page_header_links = [
        ["title" => "Create", "url" => route($routePrefix . ".create")]
    ];
?>

@include($partial_path . ".page_header")

<div class="card">
    <div class="card-body">
        <form method="GET" class="summary_search" action="{{ route($routePrefix . '.index') }}">
            <div class="row mb-4">
                <div class="col-md-3">
                    <?php $types = AutoIncreament::TYPE_LIST; ?>
                    <x-Inputs.drop-down name="type" label="Type" :value="$search['type']" :list="$types" class="form-control select2" :mandatory="true"/>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-4">
                    <div>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <span class="btn btn-secondary clear_form_search_conditions">Clear</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="index_table">
    @include($viewPrefix . ".index_table")
</div>

@endsection
