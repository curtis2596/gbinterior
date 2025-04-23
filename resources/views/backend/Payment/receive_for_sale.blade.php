@extends($layout)

@section('content')

<?php

use App\Helpers\DateUtility;

$today_date = DateUtility::getDate(null, DateUtility::DATE_OUT_FORMAT);


$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];
?>

@include($partial_path . '.page_header')

<form method="POST" class="i-validate">
    {!! csrf_field() !!}
    {{ method_field('post') }}
    <div class="row">
        <div class="offset-lg-1 col-lg-10">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down id="to_account_id" name="from_account_id" label="From Account"
                            :list="$other_account_list"
                            class="form-control select2"
                            :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down name="to_account_id" label="Receive Account"
                            :list="$our_account_list"
                            class="form-control select2"
                            :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.text-field name="voucher_date" label="Date"
                            :value="$today_date"
                            class="form-control date-picker"
                            data-date-end="0"
                            :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <x-Inputs.text-field id="amount" name="amount" label="Amount"
                                class="form-control validate-float validate-postive-only validate-more-than"
                                data-more-than-from="0"
                                :mandatory="true" />
                        </div>
                        <div class="col-md-6" style="padding-top: 25px;">                            
                            <span id="fetch_pending_amount" class="btn btn-secondary btn-sm">Re-Fetch</span>
                            <br />
                            Pending Amount : <span id="pending_amount"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <x-Inputs.text-field name="bank_transaction_no" label="Bank Transfer No. / UPI No. / Credit Card Transaction No. / Debit Card Transaction No." />
                </div>
                <div class="col-md-6">
                    <x-Inputs.text-area name="narration" label="Narration" />
                </div>
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function() {

        var pending_amount = 0;

        function fetch_pending_amount() {
            $("#pending_amount").html("");

            var to_account_id = $("#to_account_id").val();

            if (to_account_id) {
                ajaxGetJson("/ledger-payments-ajax_get_pending_receiveable_amount/" + to_account_id, function(response) {
                    pending_amount = response['data']['pending_amount'];
                    $("#pending_amount").html(response['data']['pending_amount']);
                });
            }
        }

        $("#to_account_id").change(function() {

            fetch_pending_amount();

        });

        $("#fetch_pending_amount").click(function() {

            fetch_pending_amount();
        });

        fetch_pending_amount();

        $("form").submit(function() {

            var amount = $("#amount").val();
            amount = amount ? parseFloat(amount) : 0;

            if (amount > pending_amount) {
                $.events.onUserError(`Amount : ${amount} can not be more than Pending Amount : ${pending_amount}`);
                return false;
            }

        });
    });
</script>

@endsection