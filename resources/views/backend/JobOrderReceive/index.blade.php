@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Create", "url" => route($routePrefix . ".create")],
];
?>

@include($partial_path . ".page_header")

<div class="card">
    <div class="card-body">
        <form method="GET" class="summary_search" action="{{ route($routePrefix . '.index') }}">
            <div class="row mb-2">
                <div class="col-md-3">
                    <x-Inputs.drop-down name="party_id"                        
                        label="Party"
                        :list="$partyList"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field id="receive_date" name="receive_date" 
                        label="Receive Date"
                        class="form-control date-picker"
                        autocomplete="off"
                        />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field name="challan_no" label="Challan No."/>
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field name="order_no" label="Job Order No." />
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