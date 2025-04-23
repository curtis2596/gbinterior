@extends($layout)

@section('content')

<?php
$page_header_links = [
    ["title" => "Summary", "url" => route($routePrefix . ".index")]
];
?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST" class="i-validate">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <div class="row">
        <div class="offset-lg-1 col-lg-10">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down class="select2" name="warehouse_id" id="warehouse" label="Warehouse" :list="$warehouse_list" :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down class="select2" name="process_id" label="Process" :list="$process_list" :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down class="select2" name="from_item_id" id="from_item" label="From Item" :list="$item_list" :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down class="select2" name="to_item_id" id="to_item" label="To Item" :list="$item_list" :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-sm-10">
                            <div class="form-group mb-3">
                                <x-Inputs.text-field name="from_qty" id="qty" label="From Quantity" class="form-control validate-float validate-postive-only" :mandatory="true" />
                                <span>Available Quantity : <strong id="available-qty"></strong></span><br>
                                <span id="qty-error" class="text-danger" style="display: none;"></span>
                            </div>
                        </div>
                        <div class="col-sm-2" style="padding-top: 38px;">
                            <span id="from-item-unit">Unit</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-sm-10">
                            <div class="form-group mb-3">
                                <x-Inputs.text-field name="to_qty" label="To Quantity" class="form-control validate-float validate-postive-only" :mandatory="true" />
                            </div>
                        </div>
                        <div class="col-sm-2" style="padding-top: 38px;">
                            <span id="to-item-unit">Unit</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.text-field name="wastage_qty" id="wastage" label="Wastage" class="form-control validate-float" />
                        <span id="wastage-error" class="text-danger" style="display: none;"></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <x-Inputs.text-area name="comments" label="Comment" />
                </div>
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>


<script>
    $(document).ready(function() {
        function fetchAvailableQuantity() {
            let warehouseId = $("#warehouse").val();
            let itemId = $("#from_item").val();

            if (warehouseId && itemId) {
                $.ajax({
                    url: "{{ route('warehouse.stock.available') }}",
                    type: "GET",
                    data: {
                        warehouse_id: warehouseId,
                        item_id: itemId
                    },
                    success: function(response) {
                        $("#available-qty").text(response.available_quantity);
                        $("#qty-error").hide();
                    }
                });
            }
        }

        $("#qty").on("input", function() {
            let enteredQty = parseFloat($(this).val());
            let availableQty = parseFloat($("#available-qty").text());

            if (enteredQty > availableQty) {
                $("#qty-error").text("Entered quantity exceeds available stock.").show();
            } else {
                $("#qty-error").hide();
            }
        });
        $("#wastage").on("input", function() {
            let enteredQty = parseFloat($("#qty").val());
            let wastage = parseFloat($(this).val());

            if (wastage > enteredQty) {
                $("#wastage-error").text("Wastage cannot be greater than entered quantity.").show();
            } else {
                $("#wastage-error").hide();
            }
        });

        $("form").submit(function(e) {
            let enteredQty = parseFloat($("#qty").val());
            let availableQty = parseFloat($("#available-qty").text());

            if (enteredQty > availableQty) {
                e.preventDefault();
                $("#qty-error").text("Please reduce the quantity.").show();
                $("#qty-error").css('display', 'block');
            }
        });

        $("#warehouse, #from_item").change(fetchAvailableQuantity);
        $("#from_item").change(function() {
            var v = $(this).val();
            $('#from-item-unit').html("");
            if (v) {
                ajaxGetJson("/items-ajax_get/" + v, function(response) {
                    var item = response['data'];
                    $('#from-item-unit').html(item.unit.name)
                });
            }
        });
        $("#to_item").change(function() {
            var v = $(this).val();
            $('#to-item-unit').html("");
            if (v) {
                ajaxGetJson("/items-ajax_get/" + v, function(response) {
                    var item = response['data'];
                    $('#to-item-unit').html(item.unit.name)
                });
            }
        });
    });
</script>

@endsection