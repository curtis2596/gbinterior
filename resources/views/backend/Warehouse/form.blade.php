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
        <div class="offset-lg-3 col-lg-6">
            <div class="form-group mb-3">
                <x-Inputs.drop-down id="type" name="type" label="Type" :value="$model->type"
                    :list="$typeList" class="form-control select2" :mandatory="true" />
            </div>
            <div class="form-group mb-3 type_party">
                <x-Inputs.drop-down id="party_id" name="party_id" label="Party" :value="$model->party_id"
                    :list="$partyList" class="form-control select2" />
            </div>
            <div class="form-group mb-3">
                <x-Inputs.text-field id="name" name="name" label="Warehouse Name" placeholder="Enter Warehouse Name"
                    :value="$model->name" :mandatory="true" />
            </div>
            <div class="form-group mb-3">
                <x-Inputs.drop-down id="state_id" name="state_id" label="State" :value="$model->state_id"
                    :list="$stateList" class="form-control select2 cascade"
                    data-sr-cascade-target="#city_id"
                    data-sr-cascade-url="/cities-ajax_get_list/{v}"
                    :mandatory="true" />
            </div>
            <div class="form-group mb-3">
                <?php $list = []; ?>
                <x-Inputs.drop-down id="city_id" name="city_id" label="City"
                    :value="$model->city_id" data-value="{{ $model->city_id }}"
                    :list="$list" class="form-control select2"
                    :mandatory="true" />
            </div>
            <div class="form-group mb-3">
                <x-Inputs.text-field name="address" label="Address" :value="$model->address" :mandatory="true" />
            </div>
            <div class="form-group mb-3">
                <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active" />
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
        var party = null;
        $(".cascade").cascade({
            onError: function(title, msg) {
                console.log([title, msg]);
                if (msg) {
                    $.events.onAjaxError(title, msg);
                }
            },
            beforeGet: function(src, url) {
                console.log("Cascade triggered with URL: " + url);
                $.loader.init();
                $.loader.show();
                return url;
            },
            afterGet: function(src, dest, response) {
                $.loader.hide();
                return response;
            },
            afterValueSet: function(src, dest, val) {
                if (val) {
                    dest.val(val);
                }
            },
        });

        $("#state_id").trigger("change", {
            pageLoad: true
        });

        $("#type").change(function() {

            var type = $(this).val();

            $(".type_party").hide();
            
            var cls = ".type_" + type;

            $(cls).show();

            if (type == 'party')
            {
                form_input_toggle_mandatory($("#party_id"), true);
                form_input_toggle_mandatory($("#name"), false);
            }
            else
            {
                form_input_toggle_mandatory($("#party_id"), false);
                form_input_toggle_mandatory($("#name"), true);
            }
        });

        $("#type").trigger("change");

        // $("#party_id").change(function() {

        //     var v = $(this).val();

        //     if (v)
        //     {
        //         ajaxGetJson("/parties-ajax_get/" + v, function(response){
        //             party = response['data'];
        //         });
        //     }
        // });
    });
</script>

@endpush


@endsection