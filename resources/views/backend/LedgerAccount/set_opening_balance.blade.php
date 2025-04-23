@extends($layout)

@section('content')

<?php

use App\Models\LedgerAccount;

$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];

$balance_types = laravel_constant("balance_types");
?>

<style>
    .opening_balance_type {
        width: 30% !important;
    }

    .opening_balance {
        width: 70% !important;
    }
</style>

@include($partial_path . '.page_header')

<form method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field('post') }}

    <div class="row">
        <div class="offset-lg-1 col-lg-8">
            <table id="warehouse_table" class="table table-striped table-bordered order-column">
                <thead>
                    <tr>
                        <th class="text-center" style="width : 80px;">#</th>
                        <th>Account</th>
                        <th>Account Type</th>
                        <th>Account Category</th>
                        <th style="width : 40%;">Opening Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $old_accounts = old("accounts");
                    // d($old_accounts);
                    $counter = 0;

                    foreach ($accounts as $k => $account):
                        $counter++;
                        if ($old_accounts) {
                            foreach ($old_accounts as $old_account) {
                                if (isset($old_account['id'])) {
                                    if ($old_account['id'] == $account['id']) {
                                        $account = array_merge($account, $old_account);
                                    }
                                }
                            }
                        }

                    ?>
                        <?php $id = $account['id'] ?? $k; ?>
                        <tr>
                            <td class="text-center">
                                <input type="hidden" name="accounts[{{ $id }}][id]" value="{{ $account['id'] }}" />
                                {{ $counter }}
                            </td>
                            <td>
                                {{ $account['name'] }}
                            </td>
                            <td>
                                {{ $typeList[$account['type']] ?? "" }}
                            </td>
                            <td>
                                {{ $ledgerCategoryList[$account['ledger_category_id']] ?? "" }}
                            </td>
                            <td>
                                <?php if (in_array($account['code'], LedgerAccount::ACCCOUNT_CODES_WHICH_CAN_NEVER_NEGTIVE)): ?>
                                    <?php $value = $account['opening_balance'] ?? "0"; ?>
                                    <x-Inputs.text-field name="accounts[{{ $id }}][opening_balance]"
                                        errorName="accounts.{{ $id }}.opening_balance"
                                        label=""
                                        :value="$value"
                                        class="form-control opening_balance validate-float validate-more-than-equal"
                                        data-more-than-equal-from="0"
                                        :mandatory="true" />
                                <?php else: ?>
                                <div class="input-group">
                                    <?php $value = $account['opening_balance_type'] ?? ""; ?>
                                    <x-Inputs.drop-down name="accounts[{{ $id }}][opening_balance_type]" label=""
                                        :value="$value"
                                        :list="$balance_types"
                                        class="form-control opening_balance_type"
                                        :mandatory="true" />

                                    <?php $value = $account['opening_balance'] ?? "0"; ?>
                                    <x-Inputs.text-field name="accounts[{{ $id }}][opening_balance]"
                                        errorName="accounts.{{ $id }}.opening_balance"
                                        label=""
                                        :value="$value"
                                        class="form-control opening_balance validate-float validate-more-than-equal"
                                        data-more-than-equal-from="0"
                                        :mandatory="true" />
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
    </div>
</form>

@endsection