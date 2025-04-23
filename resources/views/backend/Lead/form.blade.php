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
        <div class="offset-lg-1 col-lg-10">

            <!-- <div class="form-group mb-3">
                <h5>Voucher No. # <small class="text-muted"> {{ $model->voucher_no}} </small></h5>
            </div> -->

            <div class="row mt-2">
                <div class="offset-lg-1 col-lg-10">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <div class="form-group">
                                <x-Inputs.text-field name="date"
                                    class="form-control date-picker"
                                    label="Date" :value="old('date', $model->date ?? 'null')"
                                    :mandatory="true"
                                    autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <x-Inputs.drop-down name="level" label="Level"
                                :list="$levelList" class="form-control select2"
                                :value="old('level', $model->level ?? 'null')" :mandatory="true" />
                        </div>
                        <div class="col-md-6 mb-2">
                            <x-Inputs.drop-down id="existing-customer-fields" name="party_id" label="Party"
                                class="form-control select2" :value="old('party_id', $model->party_id ?? '')" :list="$partyList" :mandatory="true" />
                        </div>
                        <div class="col-md-6 mb-2" id="new-customer-fields" style="display: none;">
                            <div class="row">
                                <div class="col-sm-6">
                                    <x-Inputs.text-field name="customer_name"
                                        class="form-control" :value="old('customer_name', $model->customer_name ?? '')"
                                        label="Customer Name *" />
                                </div>
                                <div class="col-sm-6">
                                    <x-Inputs.text-field name="customer_email"
                                        class="form-control" :value="old('customer_email', $model->customer_email ?? '')"
                                        label="Customer Email *" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <x-Inputs.drop-down name="status" id="status-dropdown" label="Status"
                                :list="$statusList"
                                class="form-control select2"
                                :value="old('status', $model->status ?? '')" :mandatory="true" />
                        </div>
                        <div class="col-md-12 mb-2">
                            <x-Inputs.checkbox name="is_new" id="new-customer" label="New" :value="old('is_new', $model->is_new ?? false)" />
                        </div>
                        <div class="col-md-6 mb-2">
                            <x-Inputs.drop-down name="lead_source_id" label="Source"
                                :list="$sourceList" :value="old('lead_source_id', $model->lead_source_id ?? '')"
                                class="form-control select2"
                                :mandatory="true" />
                        </div>
                        <div class="col-md-6 mb-2">
                            <x-Inputs.drop-down name="follow_up_user_id" label="Follow Up By *"
                                :list="$userList" :value="old('follow_up_user_id', $model->follow_up_user_id ?? '')"
                                class="form-control select2" />
                        </div>

                        <div class="row status-dependent" id="not-interested-reason" style="display: none;">
                            <div class="col-md-6 mb-2">
                                <x-Inputs.text-field name="not_in_interested_reason" class="form-control"
                                    :value="old('not_in_interested_reason', $model->not_in_interested_reason ?? '')" label="Not Interested Reason *" />
                            </div>
                        </div>
                        
                        <div class="row status-dependent" id="follow-up-fields" style="display: none;">
                            <div class="col-md-6 mb-2">
                                <x-Inputs.text-field name="follow_up_date" class="form-control date-picker"
                                    label="Follow Up Date" :value="old('follow_up_date', $model->follow_up_date ?? '')" />
                            </div>
                            <div class="col-md-6 mb-2">
                                <x-Inputs.drop-down name="follow_up_type" label="Follow Up Type *"
                                    :list="$followtypeList" :value="old('follow_up_type', $model->follow_up_type ?? '')"
                                    class="form-control select2" />
                            </div>
                        </div>

                        <div class="row status-dependent" id="mature-fields" style="display: none;">
                            <div class="col-md-6 mb-2">
                                <x-Inputs.drop-down name="mature_action_type" label="Action To Take *"
                                    :list="$maturefieldList" :value="old('mature_action_type', $model->mature_action_type ?? '')"
                                    class="form-control select2" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <x-Inputs.checkbox name="is_include_items" id="include-items" label="Include Items" :value="old('is_include_items', $model->is_include_items ?? false)" />
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
                                        <x-Inputs.drop-down name="lead_items[item_id][]" label=""
                                            :list="$itemList" :value="old('lead_items.item_id', $model->item_id ?? '')"
                                            class="form-control will-require" />
                                    </td>
                                    <td>
                                        <x-Inputs.text-field name="lead_items[qty][]" :value="old('lead_items.qty', $model->qty ?? '')" label="" class="form-control will-require validate-float" />
                                    </td>
                                </tr>
                                <?php
                                $lead_items = old("lead_items", $lead_items ?? []);
                                ?>
                                @foreach($lead_items as $k => $lead_item)
                                <?php $id = $lead_item['id'] ?? $k; ?>
                                <tr class="" sr-id="{{ $id }}">
                                    <td>
                                        <!-- <input type="hidden" name="lead_items[{{ $id }}][id]" value="{{ $id }}" /> -->
                                        <span class="sr-table-template-delete">
                                            <i class="fas fa-times-circle text-danger icon"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $value = $lead_item['item_id'] ?? ""; ?>
                                        <x-Inputs.drop-down
                                            name="lead_items[item_id][]"
                                            label=""
                                            :list="$itemList"
                                            :value="$value"
                                            class="form-control select2 will-require"
                                            :mandatory="true" />
                                    </td>
                                    <td>
                                        <?php $value = $lead_item['qty'] ?? ''; ?>
                                        <x-Inputs.text-field
                                            name="lead_items[qty][]"
                                            label=""
                                            :value="$value"
                                            class="form-control validate-float will-require"
                                            :mandatory="true" />
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6 mb-3">
                        <x-Inputs.text-area name="comments" label="Comments" :value="$model->comments" />
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </div>
</form>

<script type="text/javascript">
    $(document).ready(function() {


        function toggleCustomerFields() {
            if ($('#new-customer').is(':checked')) {
                $('#new-customer-fields').show().find('input, select, textarea').attr("required", true);
                $('#existing-customer-fields').closest('.col-md-6').hide().find('select')
                    .removeAttr("required").val(''); 
            } else {
                $('#new-customer-fields').hide().find('input, select, textarea')
                    .removeAttr("required").val(''); 
                $('#existing-customer-fields').closest('.col-md-6').show().find('select').attr("required", true);
            }
        }

        $('#new-customer').change(toggleCustomerFields);
        toggleCustomerFields();

        function toggleStatusFields(clearValues = false) {
            let selectedStatus = $('#status-dropdown').val();

            $('.status-dependent').hide().find('input, select, textarea')
                .removeAttr("required");

            if (clearValues) {
                $('.status-dependent').find('input, select, textarea').val('');
            }

            if (selectedStatus === 'not_interested') {
                $('#not-interested-reason').show().find('input, select, textarea').attr("required", true);
            } else if (selectedStatus === 'follow_up') {
                $('#follow-up-fields').show().find('input, select, textarea').attr("required", true);
            } else if (selectedStatus === 'mature') {
                $('#mature-fields').show().find('input, select, textarea').attr("required", true);
            }
        }

        toggleStatusFields(false);

    
        $('#status-dropdown').change(function() {
            toggleStatusFields(true); 
        });



        function toggleItemsTable() {
            if ($("#include-items").is(":checked")) {
                $("#items-table-container").slideDown();
            } else {
                $("#items-table-container").slideUp(()=>{
                    $(".will-require").removeAttr("required").val("");
                });
            }
        }

        $("#include-items").change(toggleItemsTable);
        toggleItemsTable();


    });
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


        $("form").submit(function() {
            if (!form_check_unique_list(".item_id")) {
                $.events.onUserError("Duplicate Items");
                return false;
            }

            $(".sr-table-template-row select, .sr-table-template-row input").attr("disabled", true);
        });
    });
</script>

@endsection