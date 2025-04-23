@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];
?>

@include($partial_path . '.page_header')

<form action="{{ $form['url'] }}" method="POST">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <div class="row">
        <div class="offset-lg-1 col-lg-10">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down name="from_account_id" label="From Account"
                            :list="$accountList"
                            :value="$model->from_account_id"
                            class="form-control select2"
                            :mandatory="true"
                            />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down name="to_account_id" label="To Account"
                            :list="$accountList"
                            :value="$model->to_account_id"
                            class="form-control select2"
                            :mandatory="true"
                            />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.text-field name="voucher_date" label="Date"
                            :value="$model->voucher_date"
                            class="form-control date-picker"
                            data-date-end="0"
                            :mandatory="true"
                            />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.text-field name="amount" label="Amount"
                            :value="$model->amount"
                            class="form-control validate-float validate-more-than"
                            data-more-than-from="0"
                            :mandatory="true"
                            />
                    </div>
                </div>
                <div class="col-md-6">
                    <x-Inputs.text-field name="bank_transaction_no" label="Bank Transfer No. / UPI No. / Credit Card Transaction No. / Debit Card Transaction No."
                        :value="$model->bank_transaction_no"
                        />
                </div>
                <div class="col-md-6">
                    <x-Inputs.text-area name="narration" label="Narration" :value="$model->narration" :mandatory="true"/>
                </div>
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>


@endsection