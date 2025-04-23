<?php

use App\Helpers\FileUtility;
// dd($company->toArray());
?>

@extends($layout)

@section('content')

<header class="d-flex justify-content-end align-items-center mb-4 d-print-none bg-none">
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
                <b> Order No. : </b> {{ $model->voucher_no }}
                <br />
                <b> P.O. Date : </b> {{ $model->po_date }}
                <br />
                <b> Expected Delivery Date : </b> {{ if_date($model->expected_delivery_date) }}
                <br />
                <b> Shipping Instructions : </b> {{ $model->shipping_instructions }}
                <br />
                <b> Comments : </b> {{ $model->comments }}
            </td>
            <td>
                <b>Party : </b> {{ $model->party->name }}
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
                <th>Item</th>
                <th>Demand Qty</th>
                <th>Receive Qty</th>
                <th>Unit</th>
                <th>Expected Rate</th>
                <th>Expected Amount</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($model->purchaseOrderItem as $k => $purchaseOrderItem)
            <tr>
                <td>{{ $k + 1 }}</td>
                <td>{{ $purchaseOrderItem->item->name }}</td>
                <td>{{ $purchaseOrderItem->required_qty }}</td>
                <td>{{ $purchaseOrderItem->received_qty }}</td>
                <td>{{ $purchaseOrderItem->item->unit?->name }}</td>
                <td>{{ $purchaseOrderItem->rate }}</td>
                <td>{{ $purchaseOrderItem->amount }}</td>
                <td>{{ $purchaseOrderItem->comments }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="m-2 row">
        <div class="col-md-8">
            {!! $model->terms !!}
        </div>
        <div class="col-md-4 text-right">
            <h3>Total Amount : {{ $model->total_amount }} </h3>
        </div>
    </div>
</section>


@endsection