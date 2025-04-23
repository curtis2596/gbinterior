@extends($layout)

@section('content')

<?php

use App\Models\Setting;

$page_header_links = [];
$false = false;

function get_value($record_list, $field)
{
    return isset($record_list[$field]) ? $record_list[$field] : "";
}
?>

@include($partial_path . ".page_header")

<form action="{{ $form['url'] }}" method="POST">
    {!! csrf_field() !!}
    {{ method_field($form['method']) }}
    <div class="row">
        <div class="col-lg-6 col-sm-12 mb-3">
            <div class="card border border-primary">
                <div class="card-header bg-transparent border-primary">
                    <h4 class="card-title">Patterns</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <?php $field = 'item_sku_pattern';
                        $value = get_value($record_list, $field);
                        $label = Setting::LABELS[$field]; ?>
                        <x-inputs.text-field name="data[text][{{$field}}]" :label="$label" :value="$value" />
                        <div class="form-text">
                            {category}-{group}-{brand}-{item_name}-{specification}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-sm-12 mb-3">
            <div class="card border border-primary">
                <div class="card-header bg-transparent border-primary">
                    <h4 class="card-title">Other</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <?php $field = 'purchase_rate_variation_percentage_between_order_and_bill';
                        $value = get_value($record_list, $field);
                        $label = Setting::LABELS[$field]; ?>
                        <x-inputs.text-field name="data[text][{{$field}}]" :label="$label" :value="$value"
                            class="form-control validate-float" />
                    </div>
                    <div class="form-group mb-3">
                        <?php $field = 'purchase_qty_variation_percentage_between_order_and_bill';
                        $value = get_value($record_list, $field);
                        $label = Setting::LABELS[$field]; ?>
                        <x-inputs.text-field name="data[text][{{$field}}]" :label="$label" :value="$value"
                            class="form-control validate-float" />
                    </div>
                    <div class="form-group mb-3">
                        <?php $field = 'sale_rate_variation_percentage_between_order_and_bill';
                        $value = get_value($record_list, $field);
                        $label = Setting::LABELS[$field]; ?>
                        <x-inputs.text-field name="data[text][{{$field}}]" :label="$label" :value="$value"
                            class="form-control validate-float" />
                    </div>
                    <div class="form-group mb-3">
                        <?php $field = 'sale_qty_variation_percentage_between_order_and_bill';
                        $value = get_value($record_list, $field);
                        $label = Setting::LABELS[$field]; ?>
                        <x-inputs.text-field name="data[text][{{$field}}]" :label="$label" :value="$value"
                            class="form-control validate-float" />
                    </div>
                    <div class="form-group mb-3">
                        <?php $field = 'export_csv_max_record_count';
                        $value = get_value($record_list, $field);
                        $label = Setting::LABELS[$field]; ?>
                        <x-inputs.text-field name="data[text][{{$field}}]" :label="$label" :value="$value"
                            class="form-control validate-int" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>


@endsection