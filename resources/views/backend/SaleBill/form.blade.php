@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];
?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <input name="with_order" type="hidden" value="0">
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
                            class="form-control select2" :mandatory="true" />
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

            <table id="item_table" class="table table-striped table-bordered order-column template-table"
                data-sr-table-template-min-row="1" data-sr-last-id="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width : 50px;">
                            <span class="sr-table-template-add">
                                <i class="fas fa-plus-circle text-success icon"></i>
                            </span>
                        </th>
                        <th style="width : 40%;">Item</th>
                        <th style="width : 10%;">Qty</th>
                        <th style="width : 10%;">Rate</th>
                        <th style="width : 10%;">IGST %</th>
                        <th style="width : 10%;">SGST %</th>
                        <th style="width : 10%;">CGST %</th>
                        <th style="width : 10%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="sr-table-template-row hidden">
                        <td>
                            <div class="block">
                                <span class="sr-table-template-delete">
                                    <i class=" fas fa-times-circle text-danger icon"></i>
                                </span>
                                (sr-counter)
                            </div>
                        </td>
                        <td>
                            <input type="hidden" name="sale_items[(sr-id)][id]" />
                            <x-Inputs.drop-down name="sale_items[(sr-id)][item_id]" label=""
                                :list="$itemList"
                                class="form-control item_id will-require" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-id)][qty]" label=""
                                class="form-control will-require validate-float cal_amount qty" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-id)][rate]" label=""
                                class="form-control will-require validate-float cal_amount rate" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-id)][igst_per]" label=""
                                value="0"
                                class="form-control will-require validate-float validate-less-than-equal cal_amount igst_per"
                                data-less-than-equal-from="0" />
                            IGST :
                            <span class="igst"></span>
                            <input type="hidden" name="sale_items[(sr-id)][igst]" class="igst" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-id)][sgst_per]" label=""
                                value="0"
                                class="form-control will-require validate-float validate-less-than-equal cal_amount sgst_per"
                                data-less-than-equal-from="0" />
                            SGST :
                            <span class="sgst"></span>
                            <input type="hidden" name="sale_items[(sr-id)][sgst]" class="sgst" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-id)][cgst_per]" label=""
                                value="0"
                                class="form-control will-require validate-float validate-less-than-equal cal_amount cgst_per"
                                data-less-than-equal-from="0" />
                            CGST :
                            <span class="cgst"></span>
                            <input type="hidden" name="sale_items[(sr-id)][cgst]" class="cgst" />
                        </td>
                        <td>
                            <span class="amount"></span>
                            <input type="hidden" name="sale_items[(sr-id)][amount]" class="amount" />
                        </td>
                    </tr>
                    <?php
                    $sale_items = old("sale_items", $sale_items ?? []);
                    ?>
                    @foreach($sale_items as $k => $sale_item)
                    <?php $id = $sale_item['id'] ?? $k; ?>
                    <tr class="" sr-id="{{ $id }}">
                        <td>
                            <input type="hidden" name="sale_items[{{ $id }}][id]" value="{{ $sale_item['id'] }}" />
                            <span class="sr-table-template-delete">
                                <i class="fas fa-times-circle text-danger icon"></i>
                            </span>
                        </td>
                        <td>
                            <?php $value = $sale_item['item_id'] ?? ""; ?>
                            <x-Inputs.drop-down name="sale_items[{{ $id }}][item_id]"
                                errorName="sale_items.{{ $id }}.item_id"
                                label=""
                                :list="$itemList" :value="$value"
                                class="form-control select2 item_id" :mandatory="true" />
                        </td>
                        <td>
                            <?php $value = $sale_item['qty'] ?? ""; ?>
                            <x-Inputs.text-field name="sale_items[{{ $id }}][qty]"
                                errorName="sale_items.{{ $id }}.qty"
                                label=""
                                :value="$value"
                                class="form-control validate-float cal_amount qty"
                                :mandatory="true" />
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
                            <span class="amount">{{ number_format($model->amount, 2, '.', '') }}</span>
                            <input type="hidden" name="sale_items[{{ $id }}][amount]" value="{{ $model->amount }}" class="amount" />
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
        <!-- <button type="reset" class="btn btn-secondary">Reset</button> -->
    </div>
</form>

<script type="text/javascript">
    var company_state_id = '<?= $company->state_id ?>';
    var party = null;
    $(function() {
        $(".template-table").srTableTemplate({
            afterRowAdd: function(_table, last_id, _tr) {

                setTimeout(() => {
                    _tr.find("select").select2({
                        placeHolder: "Please Select",
                        theme: "bootstrap-5",
                    });
                }, 200);

                _tr.find(".will-require").attr("required", true);
            }
        });

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

        cal_total_amounts();

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

        $("input.cal_amount").trigger("keyup");

        $(document).on("change", ".item_id", function() {
            var _tr = $(this).closest("tr");
            var v = $(this).val();
            if (v && v != "0") {

                if (!party)
                {
                    $.events.onUserWarning("Please Select Party first");
                    return;   
                }

                ajaxGetJson("/items-ajax_get/" + v, function(response) {
                    _tr.find(".rate").val(response['data']['sale_rate']);
                    
                    var tax_rate = response['data']['tax_rate'];

                    if (party['city']['state_id'] == company_state_id)
                    {
                        tax_rate *= 0.5;
                        tax_rate = Math.round(tax_rate * 10) / 10;
                        _tr.find(".sgst_per, .cgst_per").val(tax_rate);

                    }
                    else
                    {
                        _tr.find(".igst_per").val(tax_rate);
                    }

                    _tr.find(".igst_per, .sgst_per, .cgst_per").attr("data-less-than-equal-from", response['data']['max_gst_per']);
                });
            }
        });

        $("#party_id").change(function(e, opt) {

            var v = $(this).val();
            if (v && v != "0") {
                ajaxGetJson("/party-ajax_get/" + v, function(response) {
                    party = response['data'];
                    console.log(party);
                    if (opt == "undefined") {
                        $(".item_id").trigger("change");
                    }
                });
            } else {
                party = null;
            }
        });

        $("#party_id").trigger("change", {
            pageLoad: true
        });

        $("form").submit(function() {
            if (!form_check_unique_list(".item_id")) {
                $.events.onUserError("Duplicate Items");
                return false;
            }

            $(".sr-table-template-row input, .sr-table-template-row select").attr("disabled", true);
        });
    });
</script>

@endsection