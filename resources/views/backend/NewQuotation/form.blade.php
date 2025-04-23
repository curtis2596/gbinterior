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
                                    label="Date"
                                    :mandatory="true"
                                    :value="old('date',$model->date)"
                                    autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-1 mb-2" style="padding-top: 33px;">
                            <x-Inputs.checkbox name="is_new" id="new-customer" label="New" :value="old('is_new', $model->is_new ?? false)" />
                        </div>
                        <div class="col-md-5 mb-2">
                            <x-Inputs.drop-down id="existing-customer-fields" name="party_id" label="Party"
                                class="form-control select2" :value="old('party_id', $model->party_id ?? '')" :list="$partyList" :mandatory="true" />
                        </div>
                        <div class="col-md-5 mb-2" id="new-customer-fields" style="display: none;">
                            <div class="row">
                                <div class="col-sm-6">
                                    <x-Inputs.text-field name="customer_name"
                                        class="form-control"
                                        :value="old('customer_name', $model->customer_name ?? '')"
                                        label="Customer Name *" />
                                </div>
                                <div class="col-sm-6">
                                    <x-Inputs.text-field name="customer_email"
                                        class="form-control"
                                        :value="old('customer_email', $model->customer_email ?? '')"
                                        label="Customer Email *" />
                                </div>
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
                                <th style="width : 50%;">Item</th>
                                <th style="width : 15%;">Price</th>
                                <th style="width : 15%;">Min. Qty To Purchase</th>
                                <th style="width : 20%;">Amount</th>
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
                                    <x-Inputs.drop-down name="quotation_items[item_id][]" label=""
                                        :list="$itemList"
                                        class="form-control" />
                                </td>
                                <td>
                                    <x-Inputs.text-field name="quotation_items[price][]" label="" class="form-control validate-float price" />
                                </td>
                                <td>
                                    <x-Inputs.text-field name="quotation_items[qty][]" label="" class="form-control validate-float qty" />
                                </td>
                                <td>
                                    <x-Inputs.text-field name="quotation_items[amount][]" label="" class="form-control validate-float amount" readonly />
                                </td>
                            </tr>
                            <?php
                            $quotation_items = old("quotation_items", $quotation_items ?? []);
                            ?>
                            @foreach($quotation_items as $k => $quotation_items)
                            <?php $id = $quotation_items['id'] ?? $k; ?>
                            <tr class="" sr-id="{{ $id }}">
                                <td>
                                    <!-- <input type="hidden" name="quotation_items[{{ $id }}][id]" value="{{ $id }}" /> -->
                                    <span class="sr-table-template-delete">
                                        <i class="fas fa-times-circle text-danger icon"></i>
                                    </span>
                                </td>
                                <td>
                                    <?php $value = old('quotation_items.item_id.' . $loop->index, $quotation_items['item_id'] ?? ""); ?>
                                    <x-Inputs.drop-down
                                        name="quotation_items[item_id][]"
                                        label=""
                                        :list="$itemList"
                                        :value="$value"
                                        class="form-control select2"
                                        :mandatory="true" />
                                </td>
                                <td>
                                    <?php $value = old('quotation_items.price.' . $loop->index, $quotation_items['price'] ?? ""); ?>
                                    <x-Inputs.text-field
                                        name="quotation_items[price][]"
                                        label=""
                                        :value="$value"
                                        class="form-control validate-float price"
                                        :mandatory="true" />
                                </td>
                                <td>
                                    <?php $value = old('quotation_items.qty.' . $loop->index, $quotation_items['qty'] ?? ""); ?>
                                    <x-Inputs.text-field
                                        name="quotation_items[qty][]"
                                        label=""
                                        :value="$value"
                                        class="form-control validate-float qty"
                                        :mandatory="true" />
                                </td>
                                <td>
                                    <?php $value = old('quotation_items.amount.' . $loop->index, $quotation_items['amount'] ?? ""); ?>
                                    <x-Inputs.text-field
                                        name="quotation_items[amount][]"
                                        label=""
                                        :value="$value"
                                        class="form-control validate-float amount"
                                        :mandatory="true" readonly />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="col-md-6 mb-2">
                        <x-Inputs.drop-down name="status" id="status-dropdown" label="Status"
                            :list="$quotationstatusList"
                            class="form-control select2"
                            :value="old('status', $model->status ?? '')"
                            :mandatory="true" />
                    </div>
                    <div class="row status-dependent" id="follow-up-fields" style="display: none;">
                        <div class="col-md-6 mb-2">
                            <x-Inputs.drop-down name="follow_up_user_id" label="Follow Up By *"
                                :list="$userList" :selected="old('follow_up_user_id', $model->follow_up_user_id ?? '')"
                                class="form-control select2" />
                        </div>
                        <div class="col-md-6 mb-2">
                            <x-Inputs.text-field name="follow_up_date" class="form-control date-picker"
                                :value="old('follow_up_date', $model->follow_up_date ?? '')" label="Follow Up Date" />
                        </div>
                        <div class="col-md-6 mb-2">
                            <x-Inputs.drop-down name="follow_up_type" label="Follow Up Type *"
                                :list="$followtypeList" :selected="old('follow_up_type', $model->follow_up_type ?? '')"
                                class="form-control select2" />
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <x-Inputs.text-area name="comments" label="Comments" :value="old('comments', $model->comments ?? '')" />
                    </div>
                    <div class="mb-3">
                        <label>Attachments</label>
                        <input type="file" name="file[]" id="files" multiple class="form-control" />
                        <div id="selected-files"></div>

                    </div>
                    @if(isset($model) && !empty($model->quotationFiles) && count($model->quotationFiles) > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($model->quotationFiles as $file)
                            <tr id="file-row-{{ $file->id }}">
                                <td>{{ basename($file->file) }}</td>
                                <td>
                                    <a href="{{ Storage::url($file->file) }}" download class="btn btn-sm btn-primary">Download</a>
                                    <button type="button" class="btn btn-sm btn-danger delete-file" data-id="{{ $file->id }}">Delete</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
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
                $('#existing-customer-fields').closest('.col-md-5').hide().find('select')
                    .removeAttr("required").val('');
            } else {
                $('#new-customer-fields').hide().find('input, select, textarea')
                    .removeAttr("required").val('');
                $('#existing-customer-fields').closest('.col-md-5').show().find('select').attr("required", true);
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

            if (selectedStatus === 'follow_up') {
                $('#follow-up-fields').show().find('input, select, textarea').attr("required", true).val('');
            }
        }

        toggleStatusFields(false);


        $('#status-dropdown').change(function() {
            toggleStatusFields(true);
        });

        $('.delete-file').click(function() {
            let fileId = $(this).data('id');
            let row = $(this).closest('tr');
            $.ajax({
                url: '{{ route("quotations.attachment.delete", "") }}/' + fileId,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    row.remove();
                },
                error: function() {
                    alert('Error deleting file');
                }
            });
        });


        $(document).on("keyup", ".price, .qty", function() {
            let row = $(this).closest("tr");
            let price = parseFloat(row.find(".price").val()) || 0;
            let qty = parseFloat(row.find(".qty").val()) || 0;
            let total = price * qty;

            row.find(".amount").val(total.toFixed(2));
        });
    });

    document.getElementById("files").addEventListener("change", function(event) {
        let fileList = event.target.files;
        let selectedFilesContainer = document.getElementById("selected-files");
        selectedFilesContainer.innerHTML = "";

        for (let i = 0; i < fileList.length; i++) {
            let fileName = fileList[i].name;
            let fileItem = document.createElement("div");
            fileItem.textContent = fileName;
            selectedFilesContainer.appendChild(fileItem);
        }
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