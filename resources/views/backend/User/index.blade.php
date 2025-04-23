<?php

use App\Helpers\FileUtility;
?>
@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Create", "url" => route($routePrefix . ".create")]
];

?>

@include($partial_path . ".page_header")

<div class="card">
    <div class="card-body new-btn-showcase">
        <form method="GET" class="summary_search" action="{{ route($routePrefix . '.index') }}">
        <input type="hidden" name="is_sort_clear" value="1"/>
            <div class="row mb-4">
                <div class="col-md-3">
                    <x-Inputs.text-field name="name" label="Name" :value="$search['name']" autocomplete="off" />
                </div>  
                <div class="col-md-3">
                    <x-Inputs.text-field name="email" label="Email" :value="$search['email']" autocomplete="off" />
                </div>   
                <div class="col-md-3">
                    <x-Inputs.drop-down name="role_id" label="Role"
                        :value="$search['role_id']"
                        :list="$role_list"
                        class="select2" />
                </div>            
                <div class="col-md-3">
                    <x-Inputs.drop-down name="is_active" label="Active"
                        :value="$search['is_active']"
                        :list="$yes_no_list"
                        class="select2" />
                </div>
            </div>
            <x-Backend.summary-search-form-footer :selectedPaginationLimit="$pagination_limit" />
        </form>
    </div>
</div>

<div id="index_table">
    @include($viewPrefix . ".index_table")
</div>

@endsection