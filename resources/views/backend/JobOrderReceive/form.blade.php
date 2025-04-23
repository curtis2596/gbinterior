@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];

$list = [];
$job_order_id = old("job_order_id",0) ; //->pluck("job_order_id")->toArray();

// dd($saved_job_order_ids);
if(empty($job_order_id) && isset($model->jobOrders->id)){
$job_order_id =  $model->jobOrders->id ; //implode(",", (array) old("job_order_id", $saved_job_order_ids));
}

// $job_order_id = implode(",", old("job_order_id", $saved_job_order_ids)); 
// dd($job_order_id)
?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <input id="id" type="hidden" value="">
    <div class="row mt-2">
        <div class="col-xs-12">

            <!-- <div class="form-group mb-3">
                <h5>Voucher No. # <small class="text-muted">  </small></h5>
            </div> -->

            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down id="party_id" name="party_id" label="Job Worker"
                            :list="$partyList" :value="$model->party_id"
                            class="form-control select2 cascade" :mandatory="true"
                            data-sr-cascade-target="#job_order_id"
                            data-sr-cascade-url="/job-orders-ajax_get/{v}" />
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <x-Inputs.drop-down id="job_order_id" name="job_order_id" label="Job Orders"
                        data-value="{{ $job_order_id }}"
                        class="form-control select2"
                        :mandatory="true" />
                </div>
                <div class="col-md-3 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="receive_date" id="receive_date"
                            class="form-control date-picker"
                            label="Receive Date" :value="if_date($model->receive_date)"
                            :mandatory="true"
                            data-date-end="0"
                            autocomplete="off" />
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="challan_no" id="challan_no"
                            label="Challan No." :value="$model->challan_no"
                            :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field name="amount" id="amount"
                            label="Amount" :value="old('amount', $model->amount)"
                            :mandatory="true" />
                    </div>
                </div>
            </div>

            <table id="item_table" class="table table-striped table-bordered order-column">
                <thead>
                    <tr>
                        <th class="text-center" style="width : 4%;">#</th>
                        <th style="width : 16%;">Order Info</th>
                        <th style="width : 20%;">Receive Item</th>
                        <th style="width : 8%;">Receive Qty</th>
                        <th style="width : 8%;">Unit</th>
                        <th style="width : 10%;">Comments</th>
                        <th style="width : 20%;">Receive At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $items = old("items", $job_order_receive_items ?? []);
                    $counter = 0;
                    ?>
                    @foreach($items as $k => $job_order_receive_item)
                    <?php
                    $counter++;
                    ?>
                    <?php $id = $job_order_receive_item['id'] ?? $k; ?>
                    <tr id="item_{{ $job_order_receive_item['job_order_item_id'] }}_{{ $job_order_receive_item['job_order_receive_id'] }}">
                        <td class="text-center">
                            <input type="hidden" name="job_items[{{ $id }}][id]" value="{{ $job_order_receive_item['id'] }}" />
                            <input type="hidden" name="job_items[{{ $id }}][job_order_item_id]" value="{{ $job_order_receive_item['job_order_item_id'] }}" />
                            <input type="hidden" name="job_items[{{ $id }}][to_item_id]" value="{{ $job_order_receive_item['to_item_id'] }}" />
                            <input type="hidden" name="job_items[{{ $id }}][job_order_item_id]" value="{{ $job_order_receive_item['job_order_item_id'] }}" />
                            <input type="hidden" name="job_items[{{ $id }}][job_order_item_id]" value="{{ $job_order_receive_item['job_order_item_id'] }}" />
                            {{ $counter }}
                        </td>
                        <td>
                            Order Info
                            <br />
                            Order No. : {{ $model->jobOrders->order_no }}
                            <br />
                            Sent Item : {{$job_order_receive_item->jobOrderItem->fromItem->name}}
                            <br />
                            Sent Qty : {{ $job_order_receive_item->jobOrderItem->from_qty }} {{ $job_order_receive_item->jobOrderItem->fromItem->unit->code }}
                            <br />
                        </td>
                        <td>
                            <?php $value = $job_order_receive_item['to_item_id'] ?? ""; ?>
                            <x-Inputs.drop-down name="job_items[{{ $id }}][to_item_id]" label=""
                                :list="$itemList" :value="$value"
                                class="form-control select2" :mandatory="true" />
                            <br />
                        </td>
                        <td>
                            <?php $value = $job_order_receive_item['to_qty'] ?? ""; ?>
                            <x-Inputs.text-field name="job_items[{{ $id }}][to_qty]"
                                class="form-control"
                                label=""
                                :value="$value"
                                :mandatory="true" />
                        </td>
                        <td>
                            <?php $value = $model['to_qty'] ?? ""; ?>
                            {{$job_order_receive_item->jobOrderItem->toItem->unit->code}}
                        </td>
                        <td>
                            <?php $value = $job_order_receive_item['comments'] ?? ""; ?>
                            <x-Inputs.text-field name="job_items[{{ $id }}][comments]"
                                class="form-control"
                                label=""
                                :value="$value"
                                :mandatory="true" />
                        </td>
                        <td>
                            @if($job_order_receive_item['receive_warehouse_id'])
                            <?php $value = $job_order_receive_item['receive_warehouse_id'] ?? ""; ?>
                            <x-Inputs.drop-down name="job_items[{{ $id }}][receive_warehouse_id]" label=""
                                :list="$warehouseList" :value="$value"
                                class="form-control select2" :mandatory="true" />
                            <br />
                            @else
                            <?php $value = $job_order_receive_item['receive_party_id'] ?? ""; ?>
                            <x-Inputs.drop-down name="job_items[{{ $id }}][receive_party_id]" label=""
                                :list="$partyList" :value="$value"
                                class="form-control select2" :mandatory="true" />
                            <br />
                            @endif
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
            <input type="hidden" name="job_items[<%= k %>][id]" value="<%= job_item.id %>" />
            <input type="hidden" name="job_items[<%= k %>][job_order_item_id]" value="<%= job_order_item_id %>">
            <input type="hidden" name="job_items[<%= k %>][job_item][order_no]" value="<%= job_item.joborder.order_no %>" />
            <input type="hidden" name="job_items[<%= k %>][job_item][from_qty]" value="<%= job_item.from_qty %>" />
            <%= counter %>
        </td>
        <td>
            Order Info
            <br />
            Order No. : <%= job_item.joborder.order_no %>
            <br />
            Sent Item : <%= job_item.from_item.display_name %>
            <br />
            Sent Qty : <%= job_item.from_qty %> <%= job_item.from_item.unit.display_name %>
            <br />
        </td>
        <td>
            <select name="job_items[<%= k %>][to_item_id]" class="form-control select2" required>
                <option value="">Please Select</option>
                <% for (var item_id in itemList) { %>
                <option value="<%= item_id %>"
                    <%= job_item.to_item.id == item_id ? 'selected' : '' %>>
                    <%= itemList[item_id] %>
                </option>
                <% } %>
            </select>
        </td>
        <td>
            <input type="text"
                name="job_items[<%= k %>][to_qty]"
                value="<%= job_item.to_qty %>"
                class="form-control will-require validate-float"
                required />
        </td>
        <td>
            <%= job_item.from_item.unit.display_name %>
        </td>
        <td>
            <input type="text"
                name="job_items[<%= k %>][comments]"
                value="<%= job_item.comments %>"
                class="form-control will-require"
                required />
        </td>
        <td>
            <% if(job_order == "warehouse"){ %>
            <select name="job_items[<%= k %>][receive_warehouse_id]" class="form-control select2" required>
                <option value="">Please Select</option>
                <% for (var warehouse_id in warehouseList) { %>
                <option value="<%= warehouse_id %>"
                    <%= job_item.receive_warehouse_id == warehouse_id ? 'selected' : '' %>>
                    <%= warehouseList[warehouse_id] %>
                </option>
                <% } %>
            </select>
            <% } else { %>
            <select name="job_items[<%= k %>][receive_party_id]" class="form-control select2" required>
                <option value="">Please Select</option>
                <% for (var party_id in partyList) { %>
                <option value="<%= party_id %>"
                    <%= job_item.receive_party_id == party_id ? 'selected' : '' %>>
                    <%= partyList[party_id] %>
                </option>
                <% } %>
            </select>
            <% } %>
        </td>
    </tr>
</noscript>


<script type="text/javascript">
    // function after_purchase_list_fetch() {
    //     var job_order_id = $("#job_order_id").val();
    //     console.log(job_order_id);
    //     if (job_order_id.length > 0) {
    //         var id = $("#id").val();
    //         ajaxGetJson("/job-orders-ajax_get_list/" + job_order_id.join(",") + "/" + id, function(response) {
    //             console.log(response);
    //             for (var k in response['data']) {
    //                 var purchase_item = response['data'][k];
    //                 console.log(purchase_item);


    //                 var _tr = $("tr#item_" + purchase_item.item_id + "_" + purchase_item.purchase_order_item_id);
    //                 console.log(_tr.length);
    //                 console.log(_tr.find("span.purchase_order_demand_qty").length);
    //                 _tr.find("span.purchase_order_voucher_no").html(purchase_item.purchase_order.voucher_no);
    //                 _tr.find("span.purchase_order_demand_qty").html(purchase_item.required_qty);
    //                 _tr.find("span.purchase_order_received_qty").html(purchase_item.received_qty);
    //                 _tr.find("span.purchase_order_pending_qty").html(purchase_item.pending_qty);
    //                 _tr.find("span.purchase_order_max_qty").html(purchase_item.max_qty);
    //                 _tr.find("input.qty").attr("data-less-than-equal-from", purchase_item.max_qty);
    //                 _tr.find("span.purchase_order_max_rate").html(purchase_item.max_rate);

    //                 _tr.find("span.unit").html(purchase_item.item.unit.name);

    //                 _tr.find("span.expected_rate").html(purchase_item.rate);
    //             }

    //             cal_total_amounts();
    //         });
    //     }
    // }



    $(function() {

        $("#party_id").change(function(e, opt) {
            let party_id = $(this).val();
            let jobOrderDropdown = $("#job_order_id");
            console.log(jobOrderDropdown);

            let amountField = $("#amount");
            let receivedate = $("#receive_date");
            let challanno = $("#challan_no");

            if (party_id) {

                $.ajax({
                    url: "/job-orders-ajax_get/" + party_id,
                    type: "GET",
                    success: function(data) {

                        jobOrderDropdown.empty();

                        $.each(data, function(id, value) {

                            jobOrderDropdown.append(new Option(value.order_no, value.id));

                            amount.append(value.amount);
                        });

                        if (typeof opt == "object" && opt.pageLoad) {
                            jobOrderDropdown.val(jobOrderDropdown.attr("data-value"));

                        } else {
                            jobOrderDropdown.trigger("change");
                            amountField.val("");
                            receivedate.val("");
                            challanno.val("");
                            if (data.length > 0) {
                                amountField.val(data[0].amount); // Set the first job order amount
                            }
                        }
                    },
                    error: function() {
                        alert("Failed to load job orders.");
                    },
                });
            }
            // else {
            //     jobOrderDropdown.empty();
            // }
        });

        $("#party_id").trigger("change", {
            pageLoad: true
        });

        $("#job_order_id").change(function() {
            var v = $(this).val();

            if (v && v.length > 0) {
                var id = $("#id").val();

                ajaxGetJson("/job-orders-ajax_get_list/" + v + id, function(response) {
                    $("#item_table tbody").html("");
                    var html = "";

                    for (var k in response['data']) {
                        let jobItem = response['data'][k];

                        // console.log(jobItem.fromItem);
                        // console.log("Job Item:", jobItem); 
                        html += ejs.render($("#item_template").html(), {
                            job_item: jobItem,
                            job_order_item_id: jobItem.id,
                            k: k,
                            job_order: response.job_order.will_receive_at_type,
                            itemList: response.itemList,
                            warehouseList: response.warehouseList,
                            partyList: response.partyList,
                            counter: parseInt(k) + 1
                        });
                        console.log("Item ID:", jobItem.id);


                    }


                    $("#item_table tbody").html(html);

                    $("#item_table  .select2").select2({
                        placeHolder: "Please Select",
                        theme: "bootstrap-5",
                    });
                    $("#item_table input").trigger("blur");
                });
            } else {
                $("#item_table tbody").html("");
            }
        });

        $("form").submit(function() {

        });
    });
</script>

@endsection