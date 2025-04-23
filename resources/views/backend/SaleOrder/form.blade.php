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
    <input id="id" type="hidden" value="{{ $model->id }}">
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
                            class="form-control select2" :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <x-Inputs.text-field name="party_order_no"
                        label="Party Order No."
                        :value="$model->party_order_no"
                        :mandatory="true"
                        autocomplete="off" />
                </div>
                <div class="col-md-6 mb-2">
                    @if($model->order_date)
                    <x-Inputs.text-field id="order_date" name="order_date"
                        class="form-control date-picker"
                        label="Order Date"
                        value="{{ if_date($model->order_date) }}"
                        :mandatory="true"
                        autocomplete="off" />
                    @else
                    <x-Inputs.text-field id="order_date" name="order_date"
                        class="form-control date-picker"
                        label="Order Date"
                        :mandatory="true"
                        data-date-end="0"
                        autocomplete="off" />
                    @endif
                </div>
                <div class="col-md-6 mb-2">                    
                    @if($model->expected_delivery_date)
                    <x-Inputs.text-field name="expected_delivery_date"
                        class="form-control date-picker"
                        label="Expected Delivery Date"
                        value="{{ if_date($model->expected_delivery_date) }}"
                        data-date-start="#order_date"
                        :mandatory="true"
                        autocomplete="off" />
                    @else
                    <x-Inputs.text-field name="expected_delivery_date"
                        class="form-control date-picker"
                        label="Expected Delivery Date"
                        :mandatory="true"
                        data-date-start="0"
                        autocomplete="off" />
                    @endif
                </div>
            </div>

            <table class="table table-striped table-bordered order-column template-table"
                data-sr-table-template-min-row="0" data-sr-last-id="0">
                <thead>
                    <tr>
                        <th class="text-center" style="width : 80px;">
                            <span class="sr-table-template-add">
                                <i class="fas fa-plus-circle text-success icon"></i>
                            </span>
                        </th>
                        <th style="width : 40%;">Item</th>
                        <th style="width : 10%;">Unit</th>
                        <th style="width : 10%;">Already Order Qty</th>
                        <th style="width : 15%;">Demand Qty</th>
                        <th style="width : 15%;">Expected Rate / Rate Fix in deal</th>
                        <th style="width : 12%;">Amount</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="sr-table-template-row hidden">
                        <td>
                            <div class="block">
                                <div class="left-block">

                                </div>
                                <div class="right-block">
                                    <span class="sr-table-template-delete">
                                        <i class=" fas fa-times-circle text-danger icon"></i>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-Inputs.drop-down name="sale_items[(sr-id)][item_id]" label=""
                                :list="$itemList"
                                class="form-control item_id will-require" />
                        </td>
                        <td>
                            <span class="unit"></span>
                        </td>
                        <td>
                            <span class="already_order_qty"></span>
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-id)][required_qty]" label="" class="form-control will-require validate-float qty" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-id)][rate]" label="" class="form-control validate-float rate" />
                        </td>
                        <td>
                            <input type="hidden" name="sale_items[(sr-id)][amount]" class="amount" />
                            <span class="amount"></span>
                        </td>
                        <td>
                            <x-Inputs.text-field name="sale_items[(sr-id)][comments]" label="" />
                        </td>
                    </tr>
                    <?php
                    $sale_items = old("sale_items", $sale_items ?? []);
                    ?>
                    @foreach($sale_items as $k => $sale_item)
                    <?php $id = $sale_item['id'] ?? $k; ?>
                    <tr class="" sr-id="{{ $id }}">
                        <td>
                            <input type="hidden" name="sale_items[{{ $id }}][id]" :value="$id" />
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
                            <span class="unit">
                                {{ $item_unit_list[$sale_item['item_id']] ?? "" }}
                            </span>
                        </td>
                        <td>
                            <span class="already_order_qty"></span>
                        </td>
                        <td>
                            <?php $value = $sale_item['required_qty'] ?? ""; ?>
                            <x-Inputs.text-field name="sale_items[{{ $id }}][required_qty]"
                                errorName="sale_items.{{ $id }}.required_qty"
                                label=""
                                :value="$value"
                                class="form-control validate-float qty"
                                :mandatory="true" />
                        </td>
                        <td>
                            <?php $value = $sale_item['rate'] ?? ""; ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][rate]"
                                errorName="sale_items.{{ $id }}.rate"
                                label=""
                                :value="$value"
                                class="form-control validate-float rate" />
                        </td>
                        <td>
                            <?php $value = $sale_item['amount'] ?? ""; ?>
                            <input type="hidden" name="sale_items[{{ $id }}][amount]" class="amount" value="{{ $value }}" />
                            <span class="amount">
                                {{ $value }}
                            </span>
                        </td>
                        <td>
                            <?php $value = $sale_item['comments']; ?>
                            <x-Inputs.text-field
                                name="sale_items[{{ $id }}][comments]"
                                errorName="sale_items.{{ $id }}.comments"
                                label=""
                                :value="$value" />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row mb-3">
                <div class="col-md-6">
                    <x-Inputs.text-area name="shipping_instructions" label="Shipping Instruction" :value="$model->shipping_instructions" />
                    <x-Inputs.text-area name="comments" label="Comments" :value="$model->comments" />
                </div>
                <div class="col-md-6 ">
                    <div class="mb-4 text-right">
                        <label class="form-label">
                            Total Amount :
                            <span class="total_amount"></span>
                            <input type="hidden" name="total_amount" class="total_amount">
                        </label>
                    </div>
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
    var item_unit_list = JSON.parse('<?= json_encode($item_unit_list) ?>');
    $(function() {
        $(".template-table").srTableTemplate({
            afterRowAdd: function(_table, last_id, _tr) {

                _tr.find("select").select2({
                    placeHolder: "Please Select",
                    theme: "bootstrap-5",
                });

                _tr.find(".will-require").attr("required", true);
            }
        });

        function get_already_order_qty(party_id, item_id, id, callback) {

            if (!party_id) {
                console.error("empty party_id");
                return;
            }

            if (!item_id) {
                console.error("empty item_id");
                return;
            }

            var url = "/sale-orders-ajax_get_already_order_qty/" + party_id + "/" + item_id;

            if (id) {
                url += "/" + id;
            }

            $.get(url, function(response) {

                ajaxHandleResponse(url, response, callback);

            }).fail(function(xhr, status, title) {
                $.loader.hide();
            });
        }

        function set_already_order_qty(_tr) {

            var id = $("#id").val();
            var party_id = $("#party_id").val();

            if (!party_id) {
                return;
            }

            var item_tr_list = {};
            if (_tr) {
                var item_id = _tr.find(".item_id").val();
                if (item_id) {
                    item_tr_list[item_id] = _tr;
                }

            } else {
                $(".template-table tbody tr").each(function() {
                    var item_id = $(this).find(".item_id").val();
                    if (item_id) {
                        item_tr_list[item_id] = $(this);
                    }
                });
            }

            if (Object.keys(item_tr_list).length > 0) {

                $.loader.init();
                $.loader.setInfo("Loading...").show();

                for (var item_id in item_tr_list) {
                    var _tr = item_tr_list[item_id];

                    _tr.find("span.already_order_qty").html("");

                    get_already_order_qty(party_id, item_id, id, function(response) {
                        var reposne_item_id = response['data']['item_id'];
                        var _tr = item_tr_list[reposne_item_id];

                        _tr.find("span.already_order_qty").html(response['data']['order_qty']);

                        delete item_tr_list[response['data']['item_id']];

                        var len = Object.keys(item_tr_list).length;
                        if (len <= 0) {
                            $.loader.hide();
                        }
                    });
                }
            }
        }

        $(document).on("change", "#party_id", function() {
            set_already_order_qty();
        });

        $(document).on("change", ".item_id", function() {
            var _tr = $(this).closest("tr");
            set_already_order_qty(_tr);

            var v = $(this).val();

            if (v)
            {
                if (typeof item_unit_list[v] != "undefined")
                {
                    _tr.find(".unit").html(item_unit_list[v]);
                }
            }
        });

        set_already_order_qty();

        $(document).on("blur", ".rate, .qty", function(){
            var _tr = $(this).closest("tr");

            var rate = _tr.find("input.rate").val();
            rate = rate ? parseFloat(rate) : 0;

            var qty = _tr.find("input.qty").val();
            qty = qty ? parseFloat(qty) : 0;

            var amt = rate * qty;

            _tr.find("span.amount").html(amt.toFixed(2));
            _tr.find("input.amount").val(amt.toFixed(2));

            var total_amt = 0;
            $("input.amount").each(function(index, ele){
                var amt = $(ele).val();

                amt = amt ? parseFloat(amt) : 0;

                total_amt += amt;
            });

            $("span.total_amount").html(total_amt.toFixed(2));
            $("input.total_amount").val(total_amt.toFixed(2));
        });

        $(".rate").trigger("blur");

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