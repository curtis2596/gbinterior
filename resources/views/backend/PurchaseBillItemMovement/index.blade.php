@extends($layout)

@section('content')

<?php

?>

@include($partial_path . ".page_header")

<div class="row mb-3">
    <div class="col-md-4 col-sm-6 col-xs-12">
        <h5>Voucher No. # <small class="text-muted"> {{ $purchaseBill->voucher_no}} </small></h5>
        <h5>Party # <small class="text-muted"> {{ $purchaseBill->party->getDisplayName() }} </small></h5>
        <h5>Party Bill No. # <small class="text-muted"> {{ $purchaseBill->party_bill_no }} </small></h5>
        <h5>Party Bill Date # <small class="text-muted"> {{ $purchaseBill->bill_date }} </small></h5>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
        <h4>Total Qty : {{ $total_qty }}</h4>
        <h4>Total Return Qty : {{ $total_return_qty }}</h4>
        <h4>Moved Qty : {{ $moved_qty }}</h4>
        <h4>Pending Qty To Move : {{ $pending_qty_to_move }}</h4>
    </div>
</div>

@include($viewPrefix . ".form")

<div id="index_table">
    @include($viewPrefix . ".index_table")
</div>

@endsection