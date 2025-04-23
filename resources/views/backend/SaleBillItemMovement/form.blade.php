<div class="row mt-2">
    <div class="offset-lg-2 col-lg-8">
        <div class="card summary-card">

            <div class="card-header">
                Form
            </div>

            <div class="card-body">
                <form action="{{ $form['url'] }}" method="POST" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    {{ method_field($form['method']) }}
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <div class="form-group">
                                <x-Inputs.drop-down id="sale_bill_item_id" name="sale_bill_item_id" label="Item"
                                    :list="$itemList"
                                    class="form-control select2" :mandatory="true" />
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-group">
                                <x-Inputs.drop-down name="warehouse_id" label="From Warehouse"
                                    :list="$warehouseList"
                                    class="form-control select2" :mandatory="true" />
                            </div>
                        </div>
                        <div class="col-md-6 mb-2" style="padding-top: 25px;">
                            Pending Qty : <span id="pending_qty">0</span> <span id="unit"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-group">
                                <x-Inputs.text-field id="qty" name="qty"
                                    label="Qty"
                                    :mandatory="true"
                                    autocomplete="off" />
                            </div>
                        </div>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
        

<script type="text/javascript">
    $(document).ready(function() {

        $("#sale_bill_item_id").change(function() {
            var v = $(this).val();

            $("#pending_qty").html(0);

            if (v) {
                ajaxGetJson("/sale-bill-item-movement-ajax_get_pending_qty/" + v, function(response) {
                    $("#pending_qty").html(response['data']['pending_qty']);
                    $("#unit").html(response['data']['unit']);
                });
            }
        });

        $("#sale_bill_item_id").trigger("change");

        $("form").submit(function() {
            var qty = $("#qty").val();
            qty = qty ? parseFloat(qty) : 0;

            var pending_qty = $("#pending_qty").text();
            pending_qty = pending_qty ? parseFloat(pending_qty) : 0;

            if (qty > pending_qty) {
                $.events.onUserError("Qty can not be more than Pending Qty");
                return false;
            }
        });
    });
</script>