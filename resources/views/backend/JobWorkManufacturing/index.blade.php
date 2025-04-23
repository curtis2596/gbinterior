@extends($layout)

@section('content')

<?php

use App\Models\InHouseManufacturing;

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
                    <x-Inputs.text-field name="challan_no" label="Challan" />
                </div>
                <div class="col-md-3 form-group mb-3">
                    <x-Inputs.text-field name="challan_date" label="Date" class="form-control date-picker" data-date-end="0" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="from_warehouse_id"
                        label="From Warehouse"
                        :value="$from_warehouse_list"
                        :list="$from_warehouse_list"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="to_warehouse_id"
                        label="To Warehouse"
                        :value="$warehouse_list"
                        :list="$warehouse_list"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="from_item_id"
                        label="From Item"
                        :list="$item_list"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="to_item_id"
                        label="To Item"
                        :list="$item_list"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="process_id"
                        label="Process"
                        :value="$process_list"
                        :list="$process_list"
                        class="form-control select2" />
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