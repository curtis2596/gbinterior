@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];

$list = [];
$saved_sale_order_ids = $model->saleBillSaleOrder->pluck("sale_order_id")->toArray();
$sale_order_ids = implode(",", old("sale_order_ids", $saved_sale_order_ids));
?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <input id="id" type="hidden" value="{{ $model->id }}">
    <input name="with_order" type="hidden" value="1">
    <div class="row mt-2">
        <div class="col-xs-12">

            <div class="form-group mb-3">
                <h5>Bill No. # <small class="text-muted"> {{ $model->voucher_no}} </small></h5>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down id="party_id" name="party_id" label="Party"
                            :list="$partyList" :value="$model->party_id"
                            class="form-control select2 cascade" :mandatory="true"
                            data-sr-cascade-target="#sale_order_ids"
                            data-sr-cascade-url="/sale-orders-ajax_get_list/{v}/{{ $sale_order_ids }}" />
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down id="sale_order_ids" name="sale_order_ids[]" label="Sale Orders"
                            :list="$list" data-value="{{ $sale_order_ids }}"
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
                        <x-Inputs.text-field name="reference_no"
                            label="Reference No."
                            :value="$model->reference_no"
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
                    $sale_items = old("sale_items", $sale_items ?? []);
                    $counter = 0;
                    ?>
                    @foreach($sale_items as $k => $sale_item)
                    <?php
                    $counter++;
                    ?>
                    <?php $id = $sale_item['id'] ?? $k; ?>
                    <tr id="item_{{ $sale_item['item_id'] }}_{{ $sale_item['sale_order_item_id'] }}">
                        <td class="text-center">
                            <input type="hidden" name="sale_items[{{ $id }}][id]" value="{{ $sale_item['id'] }}" />
                            <input type="hidden" name="sale_items[{{ $id }}][item_id]" value="{{ $sale_item['item_id'] }}" />
                            <input type="hidden" name="sale_items[{{ $id }}][sale_order_item_id]" value="{{ $sale_item['sale_order_item_id'] }}" />
                            {{ $counter }}
                        </td>
                        <td>
                            Item : {{ $itemList[$sale_item['item_id']] }}
                            <br />
                            SO Voucher No, : <span class="sale_voucher_voucher_no"></span>
                            <br />
                            SO Demand Qty : <span class="sale_voucher_demand_qty"></span>
                            <br />
                            SO Sent Qty : <span class="sale_voucher_sent_qty"></span>
                            <br />
                            SO Pending Qty : <span class="sale_voucher_pending_qty"></span>
                            <br />
                            Max Qty : <span class="sale_voucher_max_qty"></span>
                            <br />
                            Max Rate : <span class="sale_voucher_max_rate"></span>
                            <br />
                        </td>
                        <td>
                            <?php $value = $sale_item['qty'] ?? ""; ?>
                            <x-Inputs.text-field name="sale_items[{{ $id }}][qty]"
                                errorName="sale_items.{{ $id }}.qty"
                                label=""
                                :value="$value"
                                class="form-control cal_amount validate-less-than-equal qty"
                                data-less-than-equal-from="0"
                                data-less-than-equal-msg="Please Enter less than or equal to Max Qty"
                                :mandatory="true" />
                            Unit : <span class="unit"></span>
                            <br />
                        </td>
                        <td>
                            <?php $value = $sale_item['rate'] ?? ""; ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][rate]"
                                errorName="sale_items.{{ $id }}.rate"
                                label=""
                                :value="$value"
                                class="form-control validate-float cal_amount rate"
                                :mandatory="true" />

                            Expected Rate : <span class="expected_rate"></span>
                        </td>
                        <td>
                            <?php
                            $value = $sale_item['igst_per'] ?? "";
                            $max_gst_per = $itemMaxGSTList[$sale_item['item_id']];
                            ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][igst_per]"
                                errorName="sale_items.{{ $id }}.igst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount igst_per"
                                data-less-than-equal-from="{{ $max_gst_per }}" />
                            IGST :
                            <?php
                            $value = $sale_item['igst'] ?? "";
                            ?>
                            <span class="igst">{{ $value }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][igst]" value="{{ $value }}" class="igst" />
                        </td>
                        <td>
                            <?php $value = $sale_item['sgst_per'] ?? ""; ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][sgst_per]"
                                errorName="sale_items.{{ $id }}.sgst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount sgst_per"
                                data-less-than-equal-from="{{ $max_gst_per }}" />
                            SGST :
                            <?php
                            $value = $sale_item['sgst'] ?? "";
                            ?>
                            <span class="sgst">{{ $value }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][sgst]" value="{{ $value }}" class="sgst" />
                        </td>
                        <td>
                            <?php $value = $sale_item['cgst_per'] ?? ""; ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][cgst_per]"
                                errorName="sale_items.{{ $id }}.cgst_per"
                                label=""
                                :value="$value"
                                class="form-control validate-float validate-less-than-equal cal_amount cgst_per"
                                data-less-than-equal-from="{{ $max_gst_per }}" />
                            CGST :
                            <?php
                            $value = $sale_item['cgst'] ?? "";
                            ?>
                            <span class="cgst">{{ $value }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][cgst]" value="{{ $value }}" class="cgst" />
                        </td>

                        <td>
                            <?php $value = $sale_item['amount'] ?? ""; ?>
                            <span class="amount">{{ $value }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][amount]" value="{{ $value }}" class="amount" />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mb-3">
                <div class="col-md-6">
                    <x-Inputs.text-area name="narration" label="Narration" :value="$model->narration" :mandatory="true" />
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

<noscript id="item_template">
    <tr>
        <td class="text-center">
            <input type="hidden" name="sale_items[<%= k %>][id]" value="<%= sale_item.id %>" />
            <input type="hidden" name="sale_items[<%= k %>][sale_order_item_id]" value="<%= sale_item.sale_order_item_id %>" />
            <input type="hidden" name="sale_items[<%= k %>][item_id]" value="<%= sale_item.item_id %>" />
            <input type="hidden" name="sale_items[<%= k %>][sale_order][voucher_no]" value="<%= sale_item.voucher_no %>" />
            <input type="hidden" name="sale_items[<%= k %>][sale_order][required_qty]" value="<%= sale_item.required_qty %>" />
            <input type="hidden" name="sale_items[<%= k %>][sale_order][pending_qty]" value="<%= sale_item.pending_qty %>" />
            <%= counter %>
        </td>
        <td>
            Item : <%= sale_item.item.full_name %>
            <br />
            Voucher No. : <%= sale_item.sale_order.voucher_no %>
            <br />
            SO Demand Qty : <%= sale_item.required_qty %>
            <br />
            SO Sent Qty : <%= sale_item.sent_qty %>
            <br />
            SO Pending Qty : <%= sale_item.pending_qty %>
            <br />
            Max Qty : <%= sale_item.max_qty %>
            <br />
            Max Rate : <%= sale_item.max_rate %>
            <br />
        </td>
        <td>
            <input type="text" name="sale_items[<%= k %>][qty]"
                value="<%= sale_item.qty %>"
                class="form-control will-require validate-<%= sale_item.number_round_type %> validate-less-than-equal cal_amount  qty"
                data-less-than-equal-from="<%= sale_item.max_qty %>"
                data-less-than-equal-msg="Please Enter less than or equal to Max Qty"
                required="true" />

            Unit : <%= sale_item.item.unit.name %>
        </td>
        <td>
            <input type="text"
                name="sale_items[<%= k %>][rate]"
                value="<%= sale_item.rate %>"
                class="form-control will-require validate-float validate-less-than-equal cal_amount rate"
                data-less-than-equal-from="<%= sale_item.max_rate %>"
                data-less-than-equal-msg="Please Enter less than or equal to Max Rate"
                required="true" />
            Expected Rate : <%= sale_item.rate %>
        </td>
        <td>
            <input type="text"
                name="sale_items[<%= k %>][igst_per]"
                value="<%= sale_item.igst_per %>"
                class="form-control will-require validate-float validate-less-than-equal cal_amount igst_per"
                data-less-than-equal-from="<%= sale_item.item.max_gst_per %>" />

            IGST :
            <span class="igst"><%= sale_item.igst %></span>
            <input type="hidden" name="sale_items[<%= k %>][igst]" value="<%= sale_item.igst %>" class="igst" />
        </td>
        <td>
            <input type="text"
                name="sale_items[<%= k %>][sgst_per]"
                value="<%= sale_item.sgst_per %>"
                class="form-control will-require validate-float validate-less-than-equal cal_amount sgst_per"
                data-less-than-equal-from="<%= sale_item.item.max_gst_per %>" />

            SGST :
            <span class="sgst"><%= sale_item.sgst %></span>
            <input type="hidden" name="sale_items[<%= k %>][sgst]" value="<%= sale_item.sgst %>" class="sgst" />
        </td>

        <td>
            <input type="text"
                name="sale_items[<%= k %>][cgst_per]"
                value="<%= sale_item.cgst_per %>"
                class="form-control will-require validate-float validate-less-than-equal cal_amount cgst_per"
                data-less-than-equal-from="<%= sale_item.item.max_gst_per %>" />

            CGST :
            <span class="cgst"><%= sale_item.cgst %></span>
            <input type="hidden" name="sale_items[<%= k %>][cgst]" value="<%= sale_item.cgst %>" class="cgst" />
        </td>
        <td>
            <span class="amount"><%= sale_item.amount %></span>
            <input type="hidden" name="sale_items[<%= k %>][amount]" value="<%= sale_item.amount %>" class="amount" />
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

        var receivable_amount = total_amount + total_igst + total_sgst + total_cgst + freight - discount;

        $("span.receivable_amount").html(receivable_amount.toFixed(2));
        $("input.receivable_amount").val(receivable_amount.toFixed(2));
    }

    function after_sale_order_list_fetch() {
        var sale_order_ids = $("#sale_order_ids").val();
        if (sale_order_ids.length > 0) {

            if (!party) {
                $.events.onUserWarning("Please Select Party first");
                return;
            }

            var id = $("#id").val();
            ajaxGetJson("/sale-bills-ajax_get_items/" + party['id'] + "/" + sale_order_ids.join(",") + "/" + id, function(response) {
                console.log(response);
                for (var k in response['data']) {
                    var sale_item = response['data'][k];

                    var _tr = $("tr#item_" + sale_item.item_id + "_" + sale_item.sale_order_item_id);
                    _tr.find("span.sale_voucher_voucher_no").html(sale_item.sale_order.voucher_no);
                    _tr.find("span.sale_voucher_demand_qty").html(sale_item.required_qty);
                    _tr.find("span.sale_voucher_sent_qty").html(sale_item.sent_qty);
                    _tr.find("span.sale_voucher_pending_qty").html(sale_item.pending_qty);
                    _tr.find("span.sale_voucher_max_qty").html(sale_item.max_qty);
                    _tr.find("input.qty").attr("data-less-than-equal-from", sale_item.max_qty);
                    _tr.find("span.sale_voucher_max_rate").html(sale_item.max_rate);
                    _tr.find("span.unit").html(sale_item.item.unit.name);
                    _tr.find("span.expected_rate").html(sale_item.rate);
                    _tr.find("input.qty").addClass("validate-" + sale_item.number_round_type);

                    _tr.find("input.qty").trigger("keyup");
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

                    if (dest.attr("id") == "sale_order_ids") {
                        after_sale_order_list_fetch();
                    }
                }
            },
        });

        $("#party_id").change(function(e, opt) {

            var v = $(this).val();
            if (v && v != "0") {
                ajaxGetJson("/party-ajax_get/" + v, function(response) {
                    party = response['data'];
                });
            } else {
                party = null;
            }
        });

        $("#party_id").trigger("change", {
            pageLoad: true
        });


        $("#sale_order_ids").change(function() {
            var v = $(this).val();
            if (v && v.length > 0) 
            {
                if (!party)
                {
                    $.events.onUserWarning("Please Select Party first");
                    return;   
                }

                var id = $("#id").val();
                ajaxGetJson("/sale-bills-ajax_get_items/" + party['id'] + "/" + v.join(",") + "/" + id, function(response) {
                    console.log(response);

                    $("#item_table tbody").html("");
                    var html = "";

                    for (var k in response['data']) {
                        html += ejs.render($("#item_template").html(), {
                            sale_item: response['data'][k],
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