@extends($layout)

@section('content')

<?php
    $page_header_links = [
        ["title" => "Create", "url" => route($routePrefix . ".create")]
    ];
?>

@include($partial_path . '.page_header')

<div class="card">
    <div class="card-body">
        <form method="GET" class="summary_search" action="{{ route($routePrefix . '.index') }}">
            <div class="row mb-2">
                <div class="col-md-3">
                    <x-Inputs.drop-down name="from_account_id" :value="$search['from_account_id']" 
                        :list="$accountList"                         
                        label="From Account"
                        class="form-control select2" 
                        />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="to_account_id" :value="$search['to_account_id']" 
                        :list="$accountList"                         
                        label="To Account"
                        class="form-control select2" 
                        />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field name="voucher_no" :value="$search['voucher_no']"  label="Voucher No."/>
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field name="narration" :value="$search['narration']" label="Narration"  />
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <x-Inputs.text-field id="from_date" name="from_date" :value="$search['from_date']"
                        label="From Date"
                        class="form-control date-picker"
                        autocomplete="off"
                        data-date-end="input#to_date" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field id="to_date" name="to_date" :value="$search['to_date']"
                        label="To Date"
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
