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
        <h4>Moved Qty : {{ $moved_qty }}</h4>
        <h4>Return Qty : {{ $return_qty }}</h4>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field("POST") }}
    <div class="row mt-2">
        <div class="col-xs-12">
            <div class="form-group mb-3">
                <h5>Voucher No. # <small class="text-muted"> {{ $model->voucher_no}} </small></h5>
            </div>

            <div class="row mb-3">                
                <div class="col-md-3 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="voucher_date"
                            class="form-control date-picker"
                            label="Date"
                            :value="$model->voucher_date"
                            :mandatory="true"
                            autocomplete="off" />
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="refrence_no"
                            label="Refference No."
                            :value="$model->refrence_no"
                            :mandatory="true"
                            autocomplete="off" />
                    </div>
                </div>
            </div>

            <table id="item_table" class="table table-striped table-bordered order-column">
                <thead>
                    <tr>
                        <th class="text-center" style="width : 50px;">#</th>
                        <th style="width : 20%;">Item</th>
                        <th style="width : 8%;">Unit</th>
                        <th style="width : 15%;">Purchase Qty</th>
                        <th style="width : 15%;">Moved Qty</th>
                        <th style="width : 15%;">Return Qty</th>
                        <th style="width : 15%;">Rate</th>
                        <th style="width : 10%;">IGST</th>
                        <th style="width : 10%;">SGST</th>
                        <th style="width : 10%;">CGST</th>
                        <th style="width : 10%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 0;
                    ?>
                    @foreach($purchaseBill->purchaseBillItem as $k => $purchaseBillItem)
                    <?php
                    $counter++;
                    ?>
                    <?php $id = $purchaseBillItem->id; ?>
                    <tr>
                        <td class="text-center">
                            <input type="hidden" name="purchase_items[{{ $id }}][id]" value="{{ $purchaseBillItem['id'] }}" />
                            <input type="hidden" name="purchase_items[{{ $id }}][item_id]" value="{{ $purchaseBillItem['item_id'] }}" />
                            {{ $counter }}
                        </td>
                        <td>
                            {{ $purchaseBillItem->item->name }}
                        </td>
                        <td>{{ $purchaseBillItem->item->unit->code }}</td>
                        <td>
                            <span class="qty">{{ $purchaseBillItem->qty }}</span>
                        </td>
                        <td>
                            <span class="moved_qty">{{ $purchaseBillItem->moved_qty }}</span>
                        </td>
                        <td>
                            <?php 
                            $net_qty = $purchaseBillItem->qty - $purchaseBillItem->moved_qty; 
                            $validate_cls = strtoupper($purchaseBillItem->item->name) == "PC" ? "validate-int" : "validate-float";
                            $value = $purchaseBillItem->return_qty;
                            ?>
                            <x-Inputs.text-field name="purchase_items[{{ $id }}][return_qty]" label=""
                                :value="$value"
                                class="form-control $validate_cls validate-less-than-equal cal_amount return_qty"
                                data-less-than-equal-from="{{ $net_qty }}"
                                />
                        </td>
                        <td>
                            <span class="rate">{{ $purchaseBillItem->rate }}</span>
                        </td>
                        <td>
                            <span class="igst_per">{{ $purchaseBillItem->igst_per }}</span>%

                            <br/>
                            <span class="return_igst">0</span>
                            <input type="hidden" name="purchase_items[{{ $id }}][return_igst]" value="0" class="return_igst" />
                        </td>
                        <td>
                            <span class="sgst_per">{{ $purchaseBillItem->sgst_per }}</span>%

                            <br/>
                            <span class="return_sgst">0</span>
                            <input type="hidden" name="purchase_items[{{ $id }}][return_sgst]" value="0" class="return_sgst" />
                        </td>
                        <td>
                            <span class="cgst_per">{{ $purchaseBillItem->cgst_per }}</span>%

                            <br/>
                            <span class="return_cgst">0</span>
                            <input type="hidden" name="purchase_items[{{ $id }}][return_cgst]" value="0" class="return_cgst" />
                        </td>
                        <td>
                            <span class="return_amount"></span>
                            <input type="hidden" name="purchase_items[{{ $id }}][return_amount]" value="0" class="return_amount" />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mb-3">
                <div class="col-md-6">
                    <x-Inputs.text-area name="narration" label="Narration" :value="$model->narration" :mandatory="true"/>
                    <x-Inputs.text-area name="comments" label="Comments" :value="$model->comments" />
                </div>
                <div class="col-md-6 text-right">
                    <div>
                        <div class="pull-right" style="width: 400px;">
                            <div class="row mb-1">
                                <div class="col-6" style="padding-top: 7px;">
                                    Other Deduction :
                                </div>
                                <div class="col-6">
                                    <x-Inputs.text-field name="other_deduction" :value="$model->other_deduction" class="form-control validate-float cal_amount other_deduction" label="" />
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-6" style="padding-top: 7px;">
                                    Other Deduction Reason :
                                </div>
                                <div class="col-6">
                                    <x-Inputs.text-field name="other_deduction_reason" :value="$model->other_deduction_reason" class="form-control" label="" />
                                </div>
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                    <label class="form-label">
                        Total Amount :
                        <span class="total_amount"></span>
                        <input type="hidden" name="amount" class="total_amount">
                    </label>
                    <br />
                    <label class="form-label">
                        Total IGST :
                        <span class="total_igst"></span>
                        <input type="hidden" name="igst" class="total_igst">
                    </label>
                    <br />
                    <label class="form-label">
                        Total SGST :
                        <span class="total_sgst"></span>
                        <input type="hidden" name="sgst" class="total_sgst">
                    </label>
                    <br />
                    <label class="form-label">
                        Total CGST :
                        <span class="total_cgst"></span>
                        <input type="hidden" name="cgst" class="total_cgst">
                    </label>
                    <br />
                    <label class="form-label">
                        Total Receivable :
                        <span class="receivable_amount"></span>
                        <input type="hidden" name="receivable_amount" class="receivable_amount">
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function(){
        function cal_total_amounts() {
            var total_amount = 0,
                total_igst = 0,
                total_sgst = 0,
                total_cgst = 0;

            $("#item_table tbody tr").each(function() {
                var _tr = $(this);

                var qty = _tr.find("input.return_qty").val();
                qty = qty ? parseFloat(qty) : 0;

                var rate = _tr.find("span.rate").text();
                rate = rate ? parseFloat(rate) : 0;

                var amount = rate * qty;
                total_amount += amount;

                var igst = _tr.find("input.return_igst").val();
                igst = igst ? parseFloat(igst) : 0;
                total_igst += igst;

                var sgst = _tr.find("input.return_sgst").val();
                sgst = sgst ? parseFloat(sgst) : 0;
                total_sgst += sgst;

                var cgst = _tr.find("input.return_cgst").val();
                cgst = cgst ? parseFloat(cgst) : 0;
                total_cgst += cgst;
            });

            $("span.total_amount").html(total_amount.toFixed(2));
            $("input.total_amount").val(total_amount.toFixed(2));

            $("span.total_igst").html(total_igst.toFixed(2));
            $("input.total_igst").val(total_igst.toFixed(2));

            $("span.total_sgst").html(total_sgst.toFixed(2));
            $("input.total_sgst").val(total_sgst.toFixed(2));

            $("span.total_cgst").html(total_cgst.toFixed(2));
            $("input.total_cgst").val(total_cgst.toFixed(2));

            var other_deduction = $("input.other_deduction").val();
            other_deduction = other_deduction ? parseFloat(other_deduction) : 0;

            var receivable_amount = total_amount + total_igst + total_sgst + total_cgst - other_deduction;

            $("span.receivable_amount").html(receivable_amount.toFixed(2));
            $("input.receivable_amount").val(receivable_amount.toFixed(2));
        }

        

        $(document).on("keyup", "input.cal_amount", function() {
            var _tr = $(this).closest("tr");

            var qty = _tr.find("input.return_qty").val();
            qty = qty ? parseFloat(qty) : 0;

            var rate = _tr.find("span.rate").text();
            rate = rate ? parseFloat(rate) : 0;

            var amount = rate * qty;

            var igst_per = _tr.find("span.igst_per").text();
            igst_per = igst_per ? parseFloat(igst_per) : 0;
            var igst = amount * igst_per / 100;
            _tr.find("span.return_igst").html(igst.toFixed(2));
            _tr.find("input.return_igst").val(igst.toFixed(2));

            var sgst_per = _tr.find("span.sgst_per").text();
            sgst_per = sgst_per ? parseFloat(sgst_per) : 0;
            var sgst = amount * sgst_per / 100;
            _tr.find("span.return_sgst").html(sgst.toFixed(2));
            _tr.find("input.return_sgst").val(sgst.toFixed(2));

            var cgst_per = _tr.find("span.cgst_per").text();
            cgst_per = cgst_per ? parseFloat(cgst_per) : 0;
            var cgst = amount * cgst_per / 100;
            _tr.find("span.return_cgst").html(cgst.toFixed(2));
            _tr.find("input.return_cgst").val(cgst.toFixed(2));

            amount += igst + sgst + cgst;

            _tr.find("span.return_amount").html(amount.toFixed(2));
            _tr.find("input.return_amount").val(amount.toFixed(2));
        });

        $(document).on("blur", "input.cal_amount", function() {
            cal_total_amounts();
        });

        $("input.return_qty").trigger("keyup");

        cal_total_amounts();
    });
</script>

@endsection