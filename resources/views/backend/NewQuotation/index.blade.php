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
                    <x-Inputs.text-field name="customer_name" label="Customer Name" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="is_new" label="New"
                        :list="$yes_no_list" :value="$yes_no_list"
                        class="form-control select2" />
                </div>

                <div class="col-md-3 mb-3">
                    <x-Inputs.drop-down name="party_id" label="Party"
                        :list="$partyList" :value="$partyList"
                        class="form-control select2" />
                </div>

                <div class="col-md-3 mb-3">
                    <x-Inputs.drop-down name="status" label="Status"
                        :list="$quotationstatusList" :value="$quotationstatusList"
                        class="form-control select2" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.drop-down name="is_email_sent" label="Email Sent"
                        :list="$yes_no_list" :value="$yes_no_list"
                        class="form-control select2" />
                </div>
                <div class="col-md-3 mb-3">
                    <x-Inputs.drop-down name="follow_up_user_id" label="Follow Up By"
                        :list="$userListCache" :value="$userListCache"
                        class="form-control select2" />
                </div>
                <div class="col-md-3 mb-3">
                    <x-Inputs.text-field id="follow_up_date" name="follow_up_date"
                        label="Follow Up Date"
                        class="form-control date-picker"
                        autocomplete="off" />
                </div>
                <div class="col-md-3 mb-3">
                    <x-Inputs.drop-down name="follow_up_type" label="Follow Up Type"
                        :list="$followtypeList" :value="$followtypeList"
                        class="form-control select2" />
                </div>
                <div class="col-md-3 mb-3">
                    <x-Inputs.text-field name="comments" label="Comments" />
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