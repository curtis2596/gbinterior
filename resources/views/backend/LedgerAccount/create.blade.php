@extends($layout)

@section('content')

<?php

use App\Models\LedgerCategory;

$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];

?>

<style>
    .opening_balance_type{
        width: 30% !important;
    }
    .opening_balance{
        width: 70% !important;
    }
</style>

@include($partial_path . '.page_header')
@include($partial_path . '.errors')

<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <div class="row">
        <div class="offset-lg-3 col-lg-6">
            <div class="form-group mb-3">
                <x-Inputs.text-field name="name" label="Account Name" placeholder="Enter Account Name" :value="$model->account_name" />
            </div>

            <div class="form-group mb-3">
                <x-Inputs.drop-down id="ledger_category_id" name="ledger_category_id" label="Category"
                    :value="$model->ledger_category_id"
                    :list="$ledgerCategoryList"
                    class="form-control select2" />
            </div>

            <div class="form-group mb-3 bank_inputs">
                <div class="row">
                    <div class="col-sm-8">
                        <x-Inputs.text-field id="bank_branch_ifsc" name="bank_branch_ifsc" label="Bank Branch IFSC"
                            :value="$model->bank_branch_ifsc" />
                    </div>
                    <div class="col-sm-4" style="padding-top: 32px;">
                        <span class="btn btn-info" id="get_bank_detail_from_ifsc_code">Get Bank Detail</span>
                    </div>
                </div>
            </div>

            <div class="row type_party bank_inputs">
                <div class="col-sm-6">
                    <div class="form-group">
                        <x-Inputs.text-field id="bank_name" name="bank_name" label="Bank" :value="$model->bank_name" class="form-control" />
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <x-Inputs.text-field id="bank_branch_name" name="bank_branch_name" label="Bank Branch Name" :value="$model->bank_branch_name" class="form-control" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="form-group">
                        <x-Inputs.text-field id="bank_branch_address" name="bank_branch_address" label="Bank Branch Address" :value="$model->bank_branch_address" class="form-control" />
                    </div>
                </div>
            </div>

            <div class="form-group mb-3 bank_inputs">
                <x-Inputs.text-field name="bank_account_no" label="Bank Account No" :value="$model->bank_account_no" />
            </div>

            <div class="form-group mb-3">
                <div class="col-sm-6">
                    <?php
                    $list = laravel_constant("balance_types");
                    ?>
                    <label class="form-label">Opening Balance</label>
                    <div class="input-group">
                        <x-Inputs.drop-down name="opening_balance_type" label="" :list="$list" :value="$model->opening_balance_type"
                            class="form-control opening_balance_type" 
                            :mandatory="true"
                            />
                        <x-Inputs.text-field name="opening_balance" label="" :value="$model->opening_balance"
                            class="form-control opening_balance validate-float validate-more-than-equal" 
                            data-more-than-equal-from="0"
                            :mandatory="true"
                            />
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <x-Inputs.text-area name="comments" label="Comments" :value="$model->comments" />
            </div>

            <div class="form-group mb-3">
                <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active" />
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

@include($viewPrefix . ".script-for-both-add-and-edit");

@endsection