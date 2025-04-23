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
                        <x-Inputs.text-field name="challan_date" label="Date" class="form-control date-picker" data-date-end="0" :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down class="select2" name="from_warehouse_id" id="from_warehouse" label="From Warehouse" :list="$warehouse_list" :mandatory="true" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.drop-down class="select2" name="to_warehouse_id" label="To Warehouse" :list="$warehouse_list" :mandatory="true" />
                    </div>
                </div>
                <!-- <div class="col-md-6">
                    <x-Inputs.drop-down class="select2" name="item_id" id="item-select" label="Item" :list="$item_list" :mandatory="true" />
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <x-Inputs.text-field name="qty" id="qty" label="Quantity" class="form-control validate-float validate-postive-only" :mandatory="true" />
                        <span>Available Quantity : <strong id="available-qty"></strong></span><br>
                        <span id="qty-error" class="text-danger" style="display: none;"></span>
                    </div>
                </div> -->
                <div id="items-table-container">
                    <table class="table table-striped table-bordered order-column template-table"
                        data-sr-table-template-min-row="0" data-sr-last-id="0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width : 80px;">
                                    <span class="sr-table-template-add">
                                        <i class="fas fa-plus-circle text-success icon"></i>
                                    </span>
                                </th>
                                <th style="width : 50%;">Item</th>
                                <th style="width : 50%;">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="sr-table-template-row hidden">
                                <td>
                                    <div class="block">
                                        <div class="left-block"></div>
                                        <div class="right-block">
                                            <span class="sr-table-template-delete">
                                                <i class="fas fa-times-circle text-danger icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-Inputs.drop-down name="warehouse_movements[item_id][]" label=""
                                        :list="$item_list" :value="''"
                                        class="form-control will-require item-select" />
                                </td>
                                <td>
                                    <x-Inputs.text-field name="warehouse_movements[qty][]" :value="''" label=""
                                        class="form-control will-require validate-float qty" />
                                    <span>Available Quantity : <strong class="available-qty"></strong></span><br>
                                    <span class="qty-error text-danger" style="display: none;"></span>
                                </td>
                            </tr>

                        </tbody>
                    </table>
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
        function fetchAvailableQuantity(itemElement) {
            let tr = $(itemElement).closest("tr");
            let itemId = tr.find(".item-select").val();
            let warehouseId = $("#from_warehouse").val();

            if (itemId && warehouseId) {
                $.ajax({
                    url: "{{ route('warehouse.stock.available') }}",
                    type: "GET",
                    data: {
                        item_id: itemId,
                        warehouse_id: warehouseId,
                    },
                    success: function(response) {
                        tr.find(".available-qty").text(response.available_quantity);
                        tr.find(".qty-error").hide();
                    },
                    error: function(xhr) {
                        console.log("Error fetching stock:", xhr.responseText);
                    }
                });
            }
        }

        $(document).on("change", ".item-select", function() {
            fetchAvailableQuantity(this);
        });

        // function fetchAvailableQuantity() {
        //     let warehouseId = $("#from_warehouse").val();
        //     let itemId = $("#item-select").val();

        //     if (warehouseId && itemId) {
        //         $.ajax({
        //             url: "{{ route('warehouse.stock.available') }}",
        //             type: "GET",
        //             data: {
        //                 warehouse_id: warehouseId,
        //                 item_id: itemId
        //             },
        //             success: function(response) {
        //                 $("#available-qty").text(response.available_quantity);
        //                 $("#qty-error").hide();
        //             }
        //         });
        //     }
        // }

        $(document).on("keyup", ".qty", function() {
            let tr = $(this).closest("tr");
            let enteredQty = parseFloat($(this).val()) || 0;
            let availableQty = parseFloat(tr.find(".available-qty").text()) || 0;


            if (enteredQty > availableQty) {
                tr.find(".qty-error").text("Entered quantity exceeds available stock.").show();
            } else {
                tr.find(".qty-error").hide();
            }
        });

        // $(".qty").on("input", function() {
        //     let enteredQty = parseFloat($(this).val());
        //     let availableQty = parseFloat($("#available-qty").text());

        //     if (enteredQty > availableQty) {
        //         $(".qty-error").text("Entered quantity exceeds available stock.").show();
        //     } else {
        //         $(".qty-error").hide();
        //     }
        // });

        $(".template-table").srTableTemplate({
            afterRowAdd: function(_table, last_id, _tr) {
                _tr.find("select").select2({
                    placeholder: "Please Select",
                    theme: "bootstrap-5",
                });
                _tr.find(".will-require").attr("required", true).prop("disabled", false);
            }
        });

        // Validate on form submit
        $("form").submit(function(e) {
            let hasError = false;
            $(".sr-table-template-row").each(function() {
                let tr = $(this);
                let enteredQty = parseFloat(tr.find(".qty").val()) || 0;
                let availableQty = parseFloat(tr.find(".available-qty").text()) || 0;
                let item = $(this).find(".item-select").val();
                let qty = $(this).find(".qty").val();

                if (enteredQty > availableQty) {
                    tr.find(".qty-error").text("Entered quantity exceeds available stock.").show();
                }
                if (!item || !qty) {
                    $(this).remove();
                }
            });

            if (!form_check_unique_list(".item-select")) {
                $.events.onUserError("Duplicate Items");
                return false;
            }

        });

        $(".sr-table-template-row select, .sr-table-template-row input").prop("enable", true);

        // $("form").submit(function(e) {
        //     let enteredQty = parseFloat($(".qty").val());
        //     let availableQty = parseFloat($("#available-qty").text());

        //     if (enteredQty > availableQty) {
        //         e.preventDefault();
        //         $("#qty-error").text("Entered quantity exceeds available stock. Please reduce the quantity.").show();
        //         $("#qty-error").css('display', 'block');
        //     }
        // });

        // $("#from_warehouse, #item-select").change(fetchAvailableQuantity);
    });
</script>

@endsection