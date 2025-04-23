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
    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <x-Inputs.drop-down id="item_category_id" name="item_category_id" label="Category"
                    :value="$model->item_category_id"
                    :list="$item_category_list"
                    class="form-control select2"
                    :mandatory="true" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <x-Inputs.drop-down id="item_group_id" name="item_group_id" label="Group"
                    :value="$model->item_group_id"
                    :list="$itemGroupList"
                    class="form-control select2"
                    :mandatory="true" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <x-Inputs.drop-down id="brand_id" name="brand_id" label="Brand"
                    :value="$model->brand_id"
                    :list="$brandList"
                    class="form-control select2"
                    :mandatory="true" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group mb-3">
                <x-Inputs.text-field id="name" name="name" label="Item Name"
                    :value="$model->name"
                    placeholder="Enter Item Name"
                    :mandatory="true" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-3">
                <x-Inputs.text-field id="specification" name="specification" label="Specification"
                    :value="$model->specification"
                    placeholder="Enter Specification" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <x-Inputs.text-field id="sku" name="sku" label="Sku"
                    :value="$model->sku"
                    readonly="readonly" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group mb-3">
                <x-Inputs.drop-down name="unit_id" label="Unit"
                    :value="$model->unit_id"
                    :list="$unitList"
                    class="form-control select2"
                    :mandatory="true" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-3">
                <x-Inputs.text-field name="hsn_code" label="HSN Code"
                    :value="$model->hsn_code"
                    placeholder="Enter HSN Code"
                    :mandatory="true" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-3">
                <x-Inputs.text-field name="purchase_rate" label="Purchase Default Rate"
                    :value="$model->purchase_rate"
                    placeholder="Enter Rate"
                    class="form-control validate-float validate-postive-only" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-3">
                <x-Inputs.text-field name="sale_rate" label="Sale Default Rate"
                    :value="$model->sale_rate"
                    placeholder="Enter Rate"
                    class="form-control validate-float validate-postive-only" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-3">
                <x-Inputs.text-field name="tax_rate" label="Default GST %"
                    :value="$model->tax_rate"
                    placeholder="Enter GST %"
                    class="form-control validate-float validate-postive-only validate-less-than"
                    data-less-than-from="50"
                    :mandatory="true" />
            </div>
        </div>
        <div class="col-md-6" style="padding-top: 35px;">
            <div class="form-group mb-3">
                <x-Inputs.checkbox name="is_finished_item" label="Finished Item" :value="$model->is_finished_item" />
                <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active" />
            </div>
        </div>
    </div>

    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

@push('scripts')
<script>

    $(function() {

        var category, group, brand, pattern = '<?= $item_sku_pattern ?>';

        function create_sku() {
            var code = pattern;
            
            if (category) {
                code = code.replace("{category}", category['short_name']);
            }

            if (group) {
                code = code.replace("{group}", group['short_name']);
            }

            if (brand) {
                code = code.replace("{brand}", brand['short_name']);
            }

            var name = $("#name").val();
            if (name && typeof name == "string") {
                code = code.replace("{item_name}", name);
            } else {
                code = code.replace("{item_name}", "");
            }

            var specification = $("#specification").val();
            if (specification && typeof specification == "string") {
                code = code.replace("{specification}", specification);
            } else {
                code = code.replace("{specification}", "");
            }

            code = str_convert_space_to_hyphine(code);
            code = str_trim_hyphine(code);
            code = code.toLowerCase();

            $("#sku").val(code);
        }

        $("#item_category_id").change(function() {

            var v = $(this).val();

            if (v) {
                ajaxGetJson("/item-categories_ajax_get/" + v, function(response) {
                    category = response["data"];
                    create_sku();
                });
            }
        }).trigger("change", {
            pageLoad: true
        });

        $("#item_group_id").change(function() {
            var v = $(this).val();
            
            if (v) {
                ajaxGetJson("/item-groups_ajax_get/" + v, function(response) {
                    group = response["data"];
                    create_sku();
                });
            }
        }).trigger("change", {
            pageLoad: true
        });

        $("#brand_id").change(function() {
            var v = $(this).val();

            if (v) {
                ajaxGetJson("/brands_ajax_get/" + v, function(response) {
                    brand = response["data"];
                    create_sku();
                });
            }
        }).trigger("change", {
            pageLoad: true
        });

        $("#name, #specification").keyup(function() {
            create_sku();
        })
    });
</script>

@endpush


@endsection 