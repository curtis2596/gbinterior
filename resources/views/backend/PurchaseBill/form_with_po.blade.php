@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];

$list = [];
$saved_purchase_order_ids = $model->purchaseBillPurchaseOrder()->pluck("purchase_order_id")->toArray();
$purchase_order_ids = implode(",", old("purchase_order_ids", $saved_purchase_order_ids));
?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <input id="id" type="hidden" value="{{ $model->id }}">
    <input name="with_po" type="hidden" value="1">
    <div class="row mt-2">
        <div class="col-xs-12">

            <div class="form-group mb-3">
                <h5>Voucher No. # <small class="text-muted"> {{ $model->voucher_no}} </small></h5>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down id="party_id" name="party_id" label="Party"
                            :list="$partyList" :value="$model->party_id"
                            class="form-control select2 cascade" :mandatory="true"
                            data-sr-cascade-target="#purchase_order_ids"
                            data-sr-cascade-url="/purchase-orders-ajax_get_list/{v}/{{ $purchase_order_ids }}" />
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down id="purchase_order_ids" name="purchase_order_ids[]" label="Purchase Orders"
                            :list="$list" data-value="{{ $purchase_order_ids }}"
                            class="form-control select2"
                            :mandatory="true"
                            multiple="true" />
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="form-group">
                        @if($model->bill_date)
                        <x-Inputs.text-field name="bill_date"
                            class="form-control date-picker"
                            label="Bill Date"
                            :value="$model->bill_date"
                            :mandatory="true"
                            autocomplete="off" />
                        @else
                        <x-Inputs.text-field name="bill_date"
                            class="form-control date-picker"
                            label="Bill Date"
                            :mandatory="true"
                            data-date-end="0"
                            autocomplete="off" />
                        @endif
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="party_bill_no"
                            label="Party Bill No."
                            :value="$model->party_bill_no"
                            :mandatory="true"
                            autocomplete="off" />
                    </div>
                </div>
            </div>

            <table id="item_table" class="table table-striped table-bordered order-column">
                <thead>
                    <tr>
                        <th class="text-center" style="width : 50px;">#</th>
                        <th style="width : 25%;">Info</th>
                        <th style="width : 15%;">Qty</th>
                        <th style="width : 15%;">Rate</th>
                        <th style="width : 10%;">IGST%</th>
                        <th style="width : 10%;">SGST%</th>
                        <th style="width : 10%;">CGST%</th>
                        <th style="width : 10%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $purchase_items = old("purchase_items", $purchase_items ?? []);
                    $counter = 0;
                    ?>
                    @foreach($purchase_items as $k => $purchase_item)
                    <?php
                    $counter++;
                    ?>
                    <?php $id = $purchase_item['id'] ?? $k; ?>
                    <tr id="item_{{ $purchase_item['item_id'] }}_{{ $purchase_item['purchase_order_item_id'] }}">
                        <td class="text-center">
                            <input type="hidden" name="purchase_items[{{ $id }}][id]" value="{{ $purchase_item['id'] }}" />
                            <input type="hidden" name="purchase_items[{{ $id }}][item_id]" value="{{ $purchase_item['item_id'] }}" />
                            <input type="hidden" name="purchase_items[{{ $id }}][purchase_order_item_id]" value="{{ $purchase_item['purchase_order_item_id'] }}" />
                            {{ $counter }}
                        </td>
                        <td>
                            Item : {{ $itemList[$purchase_item['item_id']] }}
                            <br />
                            PO Voucher No, : <span class="purchase_order_voucher_no"></span>
                            <br />
                            PO Demand Qty : <span class="purchase_order_demand_qty"></span>
                            <br />
                            PO Received Qty : <span class="purchase_order_received_qty"></span>
                            <br />
                            PO Pending Qty : <span class="purchase_order_pending_qty"></span>
                            <br />
                            Max Qty : <span class="purchase_order_max_qty"></span>
                            <br />
                            Max Rate : <span class="purchase_order_max_rate"></span>
                            <br />
                        </td>
                        <td>
                            <?php $value = $purchase_item['qty'] ?? ""; ?>
                            <x-Inputs.text-field name="purchase_items[{{ $id }}][qty]"
                                errorName="purchase_items.{{ $id }}.qty"
                                label=""
                                :value="$value"
                                class="form-control validate-float cal_amount validate-less-than-equal qty"
                                data-less-than-equal-from="0"
                                data-less-than-equal-msg="Please Enter less than or equal to MAX Qty"
                                :mandatory="true" />
                            Unit : <span class="unit"></span>
                            <br />
                        </td>
                        <td>
                            <?php $value = $purchase_item['rate'] ?? ""; ?>
                            <x-Inputs.text-field
                                name="purchase_items[{{ $id }}][rate]"
                                errorName="purchase_items.{{ $id }}.rate"
                                label=""
                                :value="$value"
                                class="form-control validate-float cal_amount rate"
                                :mandatory="true" />

                            Expected Rate : <span class="expected_rate"></span>
                        </td>
                        <td>
                            <?php
                                $value = $purchase_item['igst_per'] ?? "";
                                $max_gst_per = $itemMaxGSTList[$purchase_item['item_id']];
                            ?>
                            <x-Inputs.text-field
                                name="purchase_items[{{ $id }}][igst_per]"
                                errorName="purchase_items.{{ $id }}.igst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount igst_per"
                                data-less-than-equal-from="{{ $max_gst_per }}" />
                            IGST :
                            <?php
                                $value = $purchase_item['igst'] ?? "";
                            ?>
                            <span class="igst">{{ $value }}</span>
                            <input type="hidden" name="purchase_items[{{ $id }}][igst]" value="{{ $value }}" class="igst" />
                        </td>
                        <td>
                            <?php $value = $purchase_item['sgst_per'] ?? ""; ?>
                            <x-Inputs.text-field
                                name="purchase_items[{{ $id }}][sgst_per]"
                                errorName="purchase_items.{{ $id }}.sgst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount sgst_per"
                                data-less-than-equal-from="{{ $max_gst_per }}" />
                            SGST :
                            <?php
                                $value = $purchase_item['sgst'] ?? "";
                            ?>
                            <span class="sgst">{{ $value }}</span>
                            <input type="hidden" name="purchase_items[{{ $id }}][sgst]" value="{{ $value }}" class="sgst" />
                        </td>
                        <td>
                            <?php $value = $purchase_item['cgst_per'] ?? ""; ?>
                            <x-Inputs.text-field
                                name="purchase_items[{{ $id }}][cgst_per]"
                                errorName="purchase_items.{{ $id }}.cgst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount cgst_per"
                                data-less-than-equal-from="{{ $max_gst_per }}" />
                            CGST :
                            <?php
                                $value = $purchase_item['cgst'] ?? "";
                            ?>
                            <span class="cgst">{{ $value }}</span>
                            <input type="hidden" name="purchase_items[{{ $id }}][cgst]" value="{{ $value }}" class="cgst" />
                        </td>

                        <td>
                            <?php $value = $purchase_item['amount'] ?? ""; ?>
                            <span class="amount">{{ $value }}</span>
                            <input type="hidden" name="purchase_items[{{ $id }}][amount]" value="{{ $value }}" class="amount" />
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
                        <div class="pull-right" style="width: 300px;">
                            <div class="row mb-1">
                                <div class="col-6" style="padding-top: 7px;">
                                    Freight :
                                </div>
                                <div class="col-6">
                                    <x-Inputs.text-field name="freight" :value="$model->freight" class="form-control cal_amount freight" label="" />
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-6" style="padding-top: 7px;">
                                    Discount :
                                </div>
                                <div class="col-6">
                                    <x-Inputs.text-field name="discount" :value="$model->discount" class="form-control cal_amount discount" label="" />
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
                        Total Payable :
                        <span class="payable_amount"></span>
                        <input type="hidden" name="payable_amount" class="payable_amount">
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

<noscript id="item_template">
    <tr>
        <td class="text-center">
            <input type="hidden" name="purchase_items[<%= k %>][id]" value="<%= purchase_item.id %>" />
            <input type="hidden" name="purchase_items[<%= k %>][purchase_order_item_id]" value="<%= purchase_item.purchase_order_item_id %>" />
            <input type="hidden" name="purchase_items[<%= k %>][item_id]" value="<%= purchase_item.item_id %>" />
            <input type="hidden" name="purchase_items[<%= k %>][purchase_order][voucher_no]" value="<%= purchase_item.voucher_no %>" />
            <input type="hidden" name="purchase_items[<%= k %>][purchase_order][required_qty]" value="<%= purchase_item.required_qty %>" />
            <input type="hidden" name="purchase_items[<%= k %>][purchase_order][pending_qty]" value="<%= purchase_item.pending_qty %>" />
            <%= counter %>
        </td>
        <td>
            Item : <%= purchase_item.item.full_name %>
            <br />
            Voucher No. : <%= purchase_item.purchase_order.voucher_no %>
            <br />
            PO Demand Qty : <%= purchase_item.required_qty %>
            <br />
            PO Received Qty : <%= purchase_item.received_qty %>
            <br />
            PO Pending Qty : <%= purchase_item.pending_qty %>
            <br />
            Max Qty : <%= purchase_item.max_qty %>
            <br />
            Max Rate : <%= purchase_item.max_rate %>
            <br />
        </td>
        <td>
            <input type="text" name="purchase_items[<%= k %>][qty]"
                value="<%= purchase_item.qty %>"
                class="form-control will-require validate-<%= purchase_item.number_round_type %>  validate-less-than-equal cal_amount  qty"
                data-less-than-equal-from="<%= purchase_item.max_qty %>"
                data-less-than-equal-msg="Please Enter less than or equal to Max Qty"
                required="true" />

            Unit : <%= purchase_item.item.unit.name %>
        </td>
        <td>
            <input type="text"
                name="purchase_items[<%= k %>][rate]"
                value="<%= purchase_item.rate %>"
                class="form-control will-require validate-float validate-less-than-equal cal_amount rate"
                data-less-than-equal-from="<%= purchase_item.max_rate %>"
                data-less-than-equal-msg="Please Enter less than or equal to Max Rate"
                required="true" />
            Expected Rate : <%= purchase_item.rate %>
        </td>
        <td>
            <input type="text"
                name="purchase_items[<%= k %>][igst_per]"
                value="<%= purchase_item.igst_per %>"
                class="form-control will-require validate-float validate-less-than-equal cal_amount igst_per"
                data-less-than-equal-from="<%= purchase_item.item.max_gst_per %>" />

            IGST :
            <span class="igst"><%= purchase_item.igst %></span>
            <input type="hidden" name="purchase_items[<%= k %>][igst]" value="<%= purchase_item.igst %>" class="igst" />
        </td>
        <td>
            <input type="text"
                name="purchase_items[<%= k %>][sgst_per]"
                value="<%= purchase_item.sgst_per %>"
                class="form-control will-require validate-float validate-less-than-equal cal_amount sgst_per"
                data-less-than-equal-from="<%= purchase_item.item.max_gst_per %>" />

            SGST :
            <span class="sgst"><%= purchase_item.sgst %></span>
            <input type="hidden" name="purchase_items[<%= k %>][sgst]" value="<%= purchase_item.sgst %>" class="sgst" />
        </td>

        <td>
            <input type="text"
                name="purchase_items[<%= k %>][cgst_per]"
                value="<%= purchase_item.cgst_per %>"
                class="form-control will-require validate-float validate-less-than-equal cal_amount cgst_per"
                data-less-than-equal-from="<%= purchase_item.item.max_gst_per %>" />

            CGST :
            <span class="cgst"><%= purchase_item.cgst %></span>
            <input type="hidden" name="purchase_items[<%= k %>][cgst]" value="<%= purchase_item.cgst %>" class="cgst" />
        </td>
        <td>
            <span class="amount"><%= purchase_item.amount %></span>
            <input type="hidden" name="purchase_items[<%= k %>][amount]" value="<%= purchase_item.amount %>" class="amount" />
        </td>
    </tr>
</noscript>

<script type="text/javascript">
    var company_state_id = '<?= $company->state_id ?>';
    var party = null;

    function cal_total_amounts() {
        var total_amount = 0,
            total_igst = 0,
            total_sgst = 0,
            total_cgst = 0;

        $("#item_table tbody tr").each(function() {
            var _tr = $(this);

            var qty = _tr.find("input.qty").val();
            qty = qty ? parseFloat(qty) : 0;

            var rate = _tr.find("input.rate").val();
            rate = rate ? parseFloat(rate) : 0;

            var amount = rate * qty;
            total_amount += amount;

            var igst = _tr.find("input.igst").val();
            igst = igst ? parseFloat(igst) : 0;
            total_igst += igst;

            var sgst = _tr.find("input.sgst").val();
            sgst = sgst ? parseFloat(sgst) : 0;
            total_sgst += sgst;

            var cgst = _tr.find("input.cgst").val();
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

        var freight = $("input.freight").val();
        freight = freight ? parseFloat(freight) : 0;

        var discount = $("input.discount").val();
        discount = discount ? parseFloat(discount) : 0;

        var payable_amount = total_amount + total_igst + total_sgst + total_cgst + freight - discount;

        $("span.payable_amount").html(payable_amount.toFixed(2));
        $("input.payable_amount").val(payable_amount.toFixed(2));
    }

    function after_purchase_list_fetch() {
        var purchase_order_ids = $("#purchase_order_ids").val();
        if (purchase_order_ids.length > 0) 
        {
            if (!party)
            {
                $.events.onUserWarning("Please Select Party first");
                return;   
            }
            
            var id = $("#id").val();
            ajaxGetJson("/purchase-bills-ajax_get_items/" + party['id'] + "/" + purchase_order_ids.join(",") + "/" + id, function(response) {
                console.log(response);
                for (var k in response['data']) {
                    var purchase_item = response['data'][k];

                    var _tr = $("tr#item_" + purchase_item.item_id + "_" + purchase_item.purchase_order_item_id);
                    console.log(_tr.length);
                    console.log(_tr.find("span.purchase_order_demand_qty").length);
                    _tr.find("span.purchase_order_voucher_no").html(purchase_item.purchase_order.voucher_no);
                    _tr.find("span.purchase_order_demand_qty").html(purchase_item.required_qty);
                    _tr.find("span.purchase_order_received_qty").html(purchase_item.received_qty);
                    _tr.find("span.purchase_order_pending_qty").html(purchase_item.pending_qty);
                    _tr.find("span.purchase_order_max_qty").html(purchase_item.max_qty);

                    _tr.find("input.qty").attr("data-less-than-equal-from", purchase_item.max_qty);
                    _tr.find("input.qty").addClass("validate-" + purchase_item.number_round_type);
                    
                    _tr.find("span.purchase_order_max_rate").html(purchase_item.max_rate);
                    
                    _tr.find("span.unit").html(purchase_item.item.unit.name);
                    
                    _tr.find("span.expected_rate").html(purchase_item.rate);
                }

                cal_total_amounts();
            });
        }
    }

    $(function() {
        $(".template-table").srTableTemplate({
            afterRowAdd: function(_table, last_id, _tr) {

                _tr.find(".will-require").attr("required", true);
            }
        });

        $(".cascade").cascade({
            onError: function(title, msg) {
                console.log([title, msg]);
                if (msg) {
                    $.events.onAjaxError(title, msg);
                }
            },
            beforeGet: function(src, url) {
                $.loader.init();
                $.loader.show();
                return url;
            },
            afterGet: function(src, dest, response) {
                $.loader.hide();
                return response;
            },
            afterValueSet: function(src, dest, val) {
                var value = dest.attr("data-value");
                if (value) {
                    var v = dest.attr("data-value").split(",");
                    dest.val(v);

                    if (dest.attr("id") == "purchase_order_ids") {
                        after_purchase_list_fetch();
                    }
                }
            },
        });

        $("#party_id").change(function(e, opt){

            var v = $(this).val();
            if (v && v != "0")
            {                
                ajaxGetJson("/party-ajax_get/" + v, function(response){
                    party = response['data'];                   
                });
            }
            else
            {
                party = null;
            }
        });

        $("#party_id").trigger("change", {pageLoad : true});

        $("#purchase_order_ids").change(function() {
            var v = $(this).val();
            if (v && v.length > 0) 
            {
                if (!party)
                {
                    $.events.onUserWarning("Please Select Party first");
                    return;   
                }

                var id = $("#id").val();
                ajaxGetJson("/purchase-bills-ajax_get_items/" + party['id'] + "/" + v.join(",") + "/" + id, function(response) {
                    console.log(response);

                    $("#item_table tbody").html("");
                    var html = "";

                    for (var k in response['data']) {
                        html += ejs.render($("#item_template").html(), {
                            purchase_item: response['data'][k],
                            k: k,
                            counter: parseInt(k) + 1
                        });
                    }

                    html += "</tr>";

                    $("#item_table tbody").html(html);

                    $("#item_table input").trigger("blur");
                });
            } else {
                $("#item_table tbody").html("");
            }
        });

        $(document).on("blur", "input.igst_per", function() {
            var _tr = $(this).closest("tr");
            var v = $(this).val();
            if (v && v != "0") {
                _tr.find("input.sgst_per, input.cgst_per").val(0);
            }
        });

        $(document).on("blur", "input.sgst_per, input.cgst_per", function() {
            var _tr = $(this).closest("tr");
            var v = $(this).val();
            if (v && v != "0") {
                _tr.find("input.igst_per").val(0);
            }
        });

        $(document).on("keyup", "input.cal_amount", function() {
            var _tr = $(this).closest("tr");

            var qty = _tr.find("input.qty").val();
            qty = qty ? parseFloat(qty) : 0;

            var rate = _tr.find("input.rate").val();
            rate = rate ? parseFloat(rate) : 0;

            var amount = rate * qty;

            var igst_per = _tr.find("input.igst_per").val();
            igst_per = igst_per ? parseFloat(igst_per) : 0;
            var igst = amount * igst_per / 100;
            _tr.find("span.igst").html(igst.toFixed(2));
            _tr.find("input.igst").val(igst.toFixed(2));

            var sgst_per = _tr.find("input.sgst_per").val();
            sgst_per = sgst_per ? parseFloat(sgst_per) : 0;
            var sgst = amount * sgst_per / 100;
            _tr.find("span.sgst").html(sgst.toFixed(2));
            _tr.find("input.sgst").val(sgst.toFixed(2));

            var cgst_per = _tr.find("input.cgst_per").val();
            cgst_per = cgst_per ? parseFloat(cgst_per) : 0;
            var cgst = amount * cgst_per / 100;
            _tr.find("span.cgst").html(cgst.toFixed(2));
            _tr.find("input.cgst").val(cgst.toFixed(2));

            amount += igst + sgst + cgst;

            _tr.find("span.amount").html(amount.toFixed(2));
            _tr.find("input.amount").val(amount.toFixed(2));
        });

        $(document).on("blur", "input.cal_amount", function() {
            cal_total_amounts();
        });

        cal_total_amounts();

        $("form").submit(function() {
            
        });
    });
</script>

@endsection