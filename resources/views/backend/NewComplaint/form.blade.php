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
    <input id="id" type="hidden" value="">
    <div class="row mt-2">
        <div class="col-xs-12">
            <div class="form-group mb-3">
                <h5>Order No. # <small class="text-muted">{{ $model->complaint_no}}</small></h5>
            </div>
            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field
                            name="date"
                            class="form-control date-picker"
                            label="Date"
                            :mandatory="true"
                            :value="old('date', if_date($model->date))"
                            autocomplete="off" />
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.drop-down
                            id="party_id"
                            name="party_id"
                            label="Customer Name"
                            :list="$partyList"
                            :value="$model->party_id ?? ''"
                            class="form-control select2 cascade"
                            :mandatory="true"
                            data-sr-cascade-target="#contact_number, #contact_person"
                            data-sr-cascade-url="/get-customer-details/{v}" />
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field
                            name="contact_number"
                            id="contact_number"
                            label="Contact Number"
                            :value="old('contact_number', $model->contact_number ?? '')" />
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-field
                            name="contact_person"
                            id="contact_person"
                            label="Contact Person"
                            :value="old('contact_person', $model->contact_person ?? '')" />
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <x-Inputs.drop-down name="status" id="status-dropdown" label="Status"
                        :list="$complaintstatusList"
                        class="form-control select2"
                        :value="old('status', $model->status ?? '')" :mandatory="true" />
                </div>
                <div class="col-md-6 mb-2">
                    <x-Inputs.drop-down name="assign_to" label="Assign to"
                        :list="$userList"
                        class="form-control select2"
                        :value="old('assign_to', $model->assign_to ?? '')"
                        :mandatory="true" />
                </div>
                <div class="col-md-1 mb-2 d-flex align-items-center">
                    <x-Inputs.checkbox name="is_free" id="is_free" label="Is free" :value="old('is_free', $model->is_free ?? false)" />
                </div>
                <div class="col-md-5 mb-2">
                    <x-Inputs.text-field name="amount" id="amount" :value="old('amount', $model->amount ?? '')" label="Amount" class="form-control validate-float" />
                </div>
                <div class="col-md-5 mb-2">
                    <x-Inputs.text-field name="sale_bill_no" id="sale_bill_no" :value="old('sale_bill_no', $model->sale_bill_no ?? '')" label="Sale Bill No." class="form-control validate-float" />
                </div>

                <div class="col-md-12 mb-2">
                    <x-Inputs.checkbox name="is_new_party" id="new-party" label="New" :value="old('is_new', $model->is_new_party ?? false)" />
                </div>
                <div id="items-table-container" style="display: none;">
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
                                        <div class="left-block">

                                        </div>
                                        <div class="right-block">
                                            <span class="sr-table-template-delete">
                                                <i class="fas fa-times-circle text-danger icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-Inputs.drop-down name="complaint_items[item_id][]" label=""
                                        :list="$itemList"
                                        class="form-control" />
                                </td>
                                <td>
                                    <x-Inputs.text-field name="complaint_items[qty][]" label="" class="form-control validate-float" />
                                </td>
                            </tr>
                            <?php
                            $complaint_items = old('complaint_items', $complaint_items ?? []);
                            ?>
                            @if (!empty($complaint_items))
                            @foreach($complaint_items as $k => $complaint_item)
                            <?php
                            $item_id = $complaint_item['item_id'] ?? null;
                            $qty = $complaint_item['qty'] ?? null;
                            $id = $complaint_item['id'] ?? $k;
                            ?>

                            @if (empty($item_id) && empty($qty))
                            @continue
                            @endif

                            <tr class="" sr-id="{{ $id }}">
                                <td>
                                    <span class="sr-table-template-delete">
                                        <i class="fas fa-times-circle text-danger icon"></i>
                                    </span>
                                </td>
                                <td>
                                    <x-Inputs.drop-down
                                        name="complaint_items[item_id][]"
                                        label=""
                                        :list="$itemList"
                                        :value="$item_id"
                                        class="form-control select2" />
                                </td>
                                <td>
                                    <x-Inputs.text-field
                                        name="complaint_items[qty][]"
                                        label=""
                                        :value="$qty"
                                        class="form-control validate-float" />
                                </td>
                            </tr>
                            @endforeach


                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12 mb-2">
                    <div class="form-group">
                        <x-Inputs.text-area
                            name="remarks"
                            label="Remarks"
                            :value="old('remarks', $model->remarks ?? '')"
                            class="form-control"
                            rows="6"
                            maxlength="500"
                            :mandatory="true" />
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

<script>
    $(document).ready(function() {
        $('#party_id').on('change', function() {
            var partyId = $(this).val();
            if (partyId) {
                $.ajax({
                    url: '/get-customer-details/' + partyId,
                    type: 'GET',
                    success: function(data) {
                        $('#contact_number').val(data.contact_number);
                        $('#contact_person').val(data.contact_person);
                    }
                });
            } else {
                $('#contact_number').val('');
                $('#contact_person').val('');
            }
        });

        var selectedValue = $('#status-dropdown').val();

        if (selectedValue === "done" || $('#new-party').prop("checked")) {
            $('#new-party').closest('.col-md-12').show();
        } else {
            $('#new-party').closest('.col-md-12').hide();
        }

        $('#status-dropdown').change(function() {
            var selectedValue = $(this).val();

            if (selectedValue === "done") {
                $('#new-party').closest('.col-md-12').show();
            } else {
                $('#new-party').prop('checked', false).closest('.col-md-12').hide();

                $("#items-table-container").hide().find("input, select").val("");

                $("#items-table-container tbody tr").not('.sr-table-template-row').remove();
            }
        });


        // $('#new-party').closest('.col-md-12').hide();


        function toggleSaleBillFields() {
            if ($('#is_free').is(':checked')) {
                $('#sale_bill_no').closest('.col-md-5').show();
                $('#amount').closest('.col-md-5').hide();
            } else {
                $('#sale_bill_no').closest('.col-md-5').hide();
                $('#amount').closest('.col-md-5').show();
            }
        }

        toggleSaleBillFields();

        $('#is_free').change(toggleSaleBillFields);

        // $('#sale_bill_no').closest('.col-md-5').hide();

    });

    // function toggleItemsTable() {
    //     if ($("#new-party").is(":checked")) {
    //         $("#items-table-container").slideDown();
    //     } else {
    //         $("#items-table-container").slideUp(() => {
    //             $(".will-require").val("");
    //         });
    //     }
    // }

    function toggleItemsTable() {
        if ($("#new-party").is(":checked")) {
            $("#items-table-container").slideDown();
            $("#items-table-container").find("input, select").prop('disabled', false);
        } else {
            $("#items-table-container").slideUp(() => {
                $("#items-table-container").find("input, select").prop('disabled', true);
                $(".will-require").val("");
            });
        }
    }



    $("#new-party").change(toggleItemsTable);
    toggleItemsTable();

    $(function() {
        $(".template-table").srTableTemplate({
            afterRowAdd: function(_table, last_id, _tr) {
                _tr.find("select").select2({
                    placeHolder: "Please Select",
                    theme: "bootstrap-5",
                });
            }
        });

        $("form").submit(function() {
            if (!form_check_unique_list(".item_id")) {
                $.events.onUserError("Duplicate Items");
                return false;
            }

            $(".sr-table-template-row select, .sr-table-template-row input").attr("disabled", true);
        });

        $('form').submit(function(event) {
            if ($('#items-table-container').is(':hidden')) {
                $('#items-table-container select, #items-table-container input').prop('disabled', true);
            }
        });
    });
</script>

@endsection