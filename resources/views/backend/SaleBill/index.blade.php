@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Create", "url" => route($routePrefix . ".create")],
    ["title" => "Create With Sale Order", "url" => route($routePrefix . ".create_with_so")],
];
?>

@include($partial_path . ".page_header")

<div class="card">
    <div class="card-body">
        <form method="GET" class="summary_search" action="{{ route($routePrefix . '.index') }}">
            <div class="row mb-2">
                <div class="col-md-3">
                    <x-Inputs.drop-down name="party_id" :value="$search['party_id']" :list="$partyList"                         
                        label="Party"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field name="voucher_no" :value="$search['voucher_no']"  label="Bill No."/>
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field name="reference_no" :value="$search['reference_no']" label="Reference No."  />
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <x-Inputs.text-field id="from_date" name="from_bill_date" :value="$search['from_bill_date']"
                        label="From Bill Date"
                        class="form-control date-picker"
                        autocomplete="off"
                        data-date-end="input#to_date" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field id="to_date" name="to_bill_date" :value="$search['to_bill_date']"
                        label="To Bill Date"
                        class="form-control date-picker"
                        autocomplete="off"
                        data-date-start="input#from_date" />
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