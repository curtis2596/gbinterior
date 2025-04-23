@extends($layout)

@section('content')

<?php

use App\Helpers\DateUtility;
use App\Models\LedgerAccount;
use App\Models\LedgerCategory;

$voucherList = laravel_constant("voucher_list");

$export_file_name = "Ledger";

// if (isset($accountList[$main_account_id])) {
//     $export_file_name .= "-" . $accountList[$main_account_id];
// }

// if ($from_date) {
//     $export_file_name .= "-From-" . $from_date;
// }

// if ($to_date) {
//     $export_file_name .= "-To-" . $to_date;
// }

?>

@include($partial_path . ".page_header")


<div class="card">
    <div class="card-body">
        <form method="GET">
            <div class="row mb-4">
                <div class="col-md-3">
                    <x-Inputs.drop-down id="main_account_id" name="main_account_id" label="Account"
                        :value="$search['main_account_id']"
                        :list="$accountList"
                        class="form-control select2"
                        :mandatory="true" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field id="from_date" name="from_date" label="From Date"
                        :value="$search['from_date']"
                        class="form-control date-picker"
                        data-date-end="input#to_date"
                        autocomplete="off"
                        :mandatory="true" />
                </div>
                <div class="col-md-3">
                    <x-Inputs.text-field id="to_date" name="to_date" label="To Date"
                        :value="$search['to_date']"
                        class="form-control date-picker"
                        data-date-start="input#from_date"
                        autocomplete="off"
                        :mandatory="true" />
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-4">
                    <div>
                        <button type="submit" class="btn btn-primary w-md">Search</button>
                        <span class="btn btn-secondary clear_form_search_conditions">Clear</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


@if (isset($records))
<div class="card mt-3">
    <div class="card-body">
        <div class="row mt-2 mb-2">
            <div class="col-md-6">
                <span class="btn btn-secondary table-export-csv"
                    data-sr-table-csv-export-target="#report"
                    data-sr-table-csv-export-filename="{{ $export_file_name }}">
                    Export CSV (JS)
                </span>
            </div>
            <div class="col-md-6">
                <table class="table table-striped table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Opening Amount</th>
                            <th>Current Amount</th>
                            <th>Closing Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ amount_with_dr_cr($opening_amount) }}</td>
                            <td>{{ amount_with_dr_cr($current_amount) }}</td>
                            <td>{{ amount_with_dr_cr($closing_amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <table class="table table-striped table-bordered table-hover mb-0" id="report">
            <thead>
                <tr>
                    <th class="center">#</th>
                    <th>Date</th>
                    <th>Voucher Type</th>
                    <th>Voucher No.</th>
                    <th>Account</th>
                    <th>Credit</th>
                    <th>Debit</th>
                    <th>Net</th>
                    <th>Narration</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 0; 
                $balance = $opening_amount;
                ?>
                @foreach($records as $record)
                <?php 
                $counter++;
                    $credit = $debit = 0;

                    $balance += $record['amount'];

                    if ($record['amount'] < 0)
                    {
                        $credit = $record['amount'];
                    }
                    else
                    {
                        $debit = $record['amount'];
                    }
                 ?>
                <tr>
                    <td class="center">{{ $counter }} </td>
                    <td>{{ DateUtility::getDate($record['voucher_date'], DateUtility::DATE_OUT_FORMAT); }}</td>
                    <td>{{ $voucherList[$record['voucher_type']] ?? "" }}</td>
                    <td>{{ $record['voucher_no'] }}</td>
                    <td>{{ $record['other_account'] }}</td>
                    <td>{{ abs($credit) }}</td>
                    <td>{{ abs($debit) }}</td>
                    <td>{{ $balance }}</td>
                    <td>{{ $record['narration'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection