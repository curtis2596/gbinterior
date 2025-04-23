@extends($layout)

@section('content')

<?php
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
                    <x-Inputs.text-field name="name" label="Name" :value="$search['name']" />
                </div>
                
                <div class="col-md-3">                    
                    <x-Inputs.drop-down name="is_active" label="Active" :value="$search['is_active']" :list="$yes_no_list" class="form-control select2"/>
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
