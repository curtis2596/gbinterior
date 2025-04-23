<?php

use App\Helpers\FileUtility;
// dd($company->toArray());
?>

@extends($layout)

@section('content')

<header class="d-flex justify-content-end align-items-center mb-4 d-print-none">
    <button class="btn btn-sm btn-info" onclick="return window.print();">Print</button>
</header>

@include($partial_path . ".page_header")

<h6 style="border-bottom: 1px solid #000; padding: 10px;">
    Order Info
</h6>

<table class="table table-bordered">
    <tbody>
        <tr>
            <td style="width : 50%">
                <b> Job Order No. : </b> {{ $model->order_no }}
                <br />
                <b> Expected Complete Date : </b> {{ if_date($model->expected_complete_date) }}
                <br />
                <b> Process : </b> {{ $model->process->name }}
                <br />
                <?php $receive_at_type_list = laravel_constant("job_order_recive_at_type_list"); ?>
                <b> Will Receive : </b> {{ $receive_at_type_list[$model->will_receive_at_type] ?? "" }}
                <br />
                <b> Comments : </b> {{ $model->comments }}
                <br />
                <b> Amount : </b> {{ $model->amount }}
            </td>
            <td>
                <b>Party : </b> {{ $model->party->name }}
                <br />
                <b>Party Order No. : </b> {{ $model->party_order_no }}
                <br />               
                <b>City : </b> {{ $model->party->city?->name }}
                <br />
                <b>Address : </b> {{ $model->party->address }}
                <br />
                <b>Mobile : </b> {{ $model->party->mobile }}
                <br />
                <b>Phone : </b> {{ $model->party->phone }}
            </td>
        </tr>
    </tbody>
</table>

<h6 style="border-bottom: 1px solid #000; padding: 10px;">
    Items Info
</h6>

<section class="table-responsive rounded mb-2">
    <table class="table table-bordered" style="min-width: 60rem;">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>From Item</th>
                <th>From Qty</th>
                <th>Expected Receive Item</th>
                <th>Expected Receive Qty</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($model->jobOrderItem as $k => $jobOrderItem)
            <tr>
                <td>{{ $k + 1 }}</td>
                <td>{{ $jobOrderItem->fromitem->getDisplayName() }}</td>
                <td>{{ $jobOrderItem->from_qty }}</td>
                <td>{{ $jobOrderItem->toitem->getDisplayName() }}</td>
                <td>{{ $jobOrderItem->to_qty }}</td>
                <td>{{ $jobOrderItem->comments }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</section>

@endsection