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
                <h5>Order No. # <small class="text-muted">{{ $model->order_no}}</small></h5>
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
                    @if($model->expected_complete_date)
                    <x-Inputs.text-field id="expected_complete_date" name="expected_complete_date"
                        class="form-control date-picker"
                        label="Expected Complete Date"
                        value="{{ if_date($model->expected_complete_date) }}"
                        :mandatory="true"
                        autocomplete="off" />
                    @else
                    <x-Inputs.text-field name="expected_complete_date"
                        class="form-control date-picker"
                        label="Expected Complete Date"
                        :mandatory="true"
                        data-date-start="0"
                        autocomplete="off" />
                    @endif
                </div>
                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down name="process_id" label="Process"
                            :list="$processList" :value="$model->process_id"
                            class="form-control select2" :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="form-group mb-3">
                        <x-Inputs.text-field name="amount" label="Amount" :value="$model->amount" class="form-control validate-float" />
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <x-Inputs.text-area name="comments" :value="$model->comments" label="Comments" />
                    </div>
                    <div class="col-md-6">
                        <?php $receive_at_type_list = laravel_constant("job_order_recive_at_type_list"); ?>
                    <x-Inputs.drop-down id="will_receive_at_type" name="will_receive_at_type" label="Will Receive On"
                            :list="$receive_at_type_list" 
                            :value="$model->will_receive_at_type"
                            class="form-control select2" 
                            :mandatory="true" 
                        />
                    </div>
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
                        <th style="width : 30%;">From Item</th>
                        <th style="width : 10%;">From Qty</th>
                        <th style="width : 30%;">Expected Received Item</th>
                        <th style="width : 10%;">Expected Received Qty</th>
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
                            <input name="job_items[(sr-id)][id]" value="0" type="hidden">

                        </td>
                        <td>
                            <x-Inputs.drop-down name="job_items[(sr-id)][from_item_id]" label=""
                                :list="$itemList"
                                class="form-control item_id will-require" />
                        </td>                      
                        <td>
                            <x-Inputs.text-field name="job_items[(sr-id)][from_qty]" label="" class="form-control will-require validate-float" />
                        </td>
                        <td>
                            <x-Inputs.drop-down name="job_items[(sr-id)][to_item_id]" label=""
                                :list="$itemList"
                                class="form-control item_id will-require" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="job_items[(sr-id)][to_qty]" value="0" label="" class="form-control validate-float will-require" />
                        </td>
                        <td>
                            <x-Inputs.text-field name="job_items[(sr-id)][comments]" label="" />
                        </td>
                    </tr>
                    <?php
                    $job_items = old("job_items", $job_items ?? []);
                    ?>
                    @foreach($job_items as $k => $job_item)
                    <?php $id = $job_item['id'] ?? $k; ?>
                    <tr class="" sr-id="{{ $id }}">
                        <td>
                            <input type="hidden" name="job_items[{{ $id }}][id]" value="{{ $job_item['id'] }}" />
                            <span class="sr-table-template-delete">
                                <i class="fas fa-times-circle text-danger icon"></i>
                            </span>
                        </td>
                        <td>
                            <?php $value = $job_item['from_item_id'] ?? ""; ?>
                            <x-Inputs.drop-down name="job_items[{{ $id }}][from_item_id]"
                                errorName="job_items.{{ $id }}.from_item_id"
                                label=""
                                :list="$itemList" :value="$value"
                                class="form-control select2 from_item_id" :mandatory="true" />
                        </td>                        
                        <td>
                            <?php $value = $job_item['from_qty'] ?? ""; ?>
                            <x-Inputs.text-field name="job_items[{{ $id }}][from_qty]"
                                errorName="job_items.{{ $id }}.from_qty"
                                label=""
                                :value="$value"
                                class="form-control validate-float"
                                :mandatory="true" />
                        </td>
                        <td>
                            <?php $value = $job_item['to_item_id'] ?? ""; ?>
                            <x-Inputs.drop-down name="job_items[{{ $id }}][to_item_id]"
                                errorName="job_items.{{ $id }}.to_item_id"
                                label=""
                                :list="$itemList" :value="$value"
                                class="form-control select2 to_item_id" />
                        </td>
                        <td>
                            <?php $value = $job_item['to_qty'] ?? ""; ?>
                            <x-Inputs.text-field
                                name="job_items[{{ $id }}][to_qty]"
                                errorName="job_items.{{ $id }}.to_qty"
                                label=""
                                :value="$value"
                                class="form-control validate-float" />
                        </td>
                        <td>
                            <?php $value = $job_item['comments']; ?>
                            <x-Inputs.text-field
                                name="job_items[{{ $id }}][comments]"
                                errorName="job_items.{{ $id }}.comments"
                                label=""
                                :value="$value" />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

<script type="text/javascript">
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
                var item_id = _tr.find(".to_item_id").val();
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
                        console.log(response);
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
            // set_already_order_qty();
        });

        $(document).on("change", ".from_item_id, .to_item_id", function() {
            // set_already_order_qty($(this).closest("tr"));
        });

        // set_already_order_qty();

        $("form").submit(function() {

            $(".sr-table-template-row input, .sr-table-template-row select").attr("disabled", true);
        });
    });
</script>

@endsection