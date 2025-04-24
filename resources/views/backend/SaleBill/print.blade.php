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
                <b> Bill No. : </b> {{ $model->voucher_no }}
                <br />
                <b> Bill Date : </b> {{ $model->bill_date }}
                <br />
                <b> Dispatch From : </b> {{ $model->dispatch }}
                <br />
                <b> Delivered At : </b> {{ $model->delivered }}
                <br />
               
                <b> Vehicle No : </b> {{ $model->vehicle_no }}
                <br />
                <b> Naration : </b> {{ $model->naration }}
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
                <th>Unit</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($model->saleBillItem as $k => $saleBillItem)
            <tr>
                <td>{{ $k + 1 }}</td>
                <td>{{ $saleBillItem->item->name }}</td>
                <td>{{ $saleBillItem->item->unit?->name }}</td>
                <td>{{ $saleBillItem->qty }}</td> 
                <td>{{ $saleBillItem->rate }}</td>
                <td>{{ $saleBillItem->amount }}</td>
                <td>{{ $saleBillItem->comments }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="m-2 row">
        <div class="col-md-7">
            {!! $model->terms !!}
        </div>
        <div class="col-md-5 text-right">
            <label class="form-label">
                <h5>Total Freight : {{ $model->freight }} </h5>  
            </label>
            <br />
            <label class="form-label">
                <h5>Total Discount : {{ $model->discount }} </h5>  
            </label>
            <br />
            <label class="form-label">
                <h5>Total Amount : {{ $model->amount }} </h5>  
            </label>
            <br />
            <label class="form-label">
                <h5>Total IGST : {{ $model->igst }} </h5> 
            </label>
            <br />
            <label class="form-label">
                <h5>Total SGST : {{ $model->sgst }} </h5>
            </label>
            <br />
            <label class="form-label">
                <h5>Total CGST :  {{ $model->cgst }} </h5>
            </label>
            <br /> 
                <h5>Total Receivable  :  {{ $model->receivable_amount }} </h5>
            </label>
        </div>
        
    </div>
</section>


@endsection