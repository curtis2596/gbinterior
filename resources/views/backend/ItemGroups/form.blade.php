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
                <x-Inputs.text-field id="name" name="name" label="Item Group Name" placeholder="Enter Item Group Name"
                    :value="$model->name" :mandatory="true" />
            </div> 
            <div class="form-group mb-3">
                <x-Inputs.text-field id="short_name" name="short_name" label="Item Group Short Name" placeholder="Enter Item Short Group Name"
                    :value="$model->short_name" :mandatory="true" />
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