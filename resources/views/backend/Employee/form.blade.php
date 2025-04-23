@extends($layout)

@section('content')
    <?php
    $page_header_links = [['title' => 'Summary', 'url' => route($routePrefix . '.index')]];
    ?>

    @include($partial_path . '.page_header')

    <x-Backend.form-errors />

    <form action="{{ $form['url'] }}" method="POST" class="" enctype="multipart/form-data">
        {!! csrf_field() !!}
        {{ method_field($form['method']) }}

        <div class="row">
            <div class="offset-md-2 col-md-8">
                <div class="card">
                    <div class="card-header card-no-border pb-0">
                        <h3>Basic</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <label for="">Type</label>
                                    <select name="type" id="" class="select2">
                                        <option value="">Select</option>
                                        @foreach ($records['types'] as $type)
                                            <option value="{{ $type }}"
                                                @if ($model->type == $type) selected @endif>{{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <x-Inputs.text-field name="name" label="Name" :value="$model->name"
                                        placeholder="Enter Name" autocomplete="off" mandatory="true" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <x-Inputs.drop-down name="department_id" label="Department" :list="$records['departments']"
                                        class="select2" mandatory="true" :value="$model->department_id" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <x-Inputs.drop-down name="designation_id" label="Designation" :list="$records['designations']"
                                        class="select2" mandatory="true" :value="$model->designation_id" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <x-Inputs.drop-down name="current_state_id" id="cState" label="Current State"
                                        :list="$records['states']" class="select2" mandatory="true" :value="$model->current_state_id" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3 editCity">
                                    <x-Inputs.drop-down name="current_city_id" id="cCity" label="Current City"
                                        class="select2" mandatory="true" :data-id="$model->current_city_id" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <x-Inputs.drop-down name="permanent_state_id" id="pState" label="Permanent State"
                                        :list="$records['states']" class="select2" mandatory="true" :value="$model->permanent_state_id" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <x-Inputs.drop-down name="permanent_city_id" id="pCity" label="Permanent City"
                                        class="select2" mandatory="true" :data-id="$model->permanent_city_id" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <x-Inputs.drop-down name="type_id" label="Type" :list="$records['dtypes']" class="select2"
                                        mandatory="true" :value="$model->type_id" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-3">
                                    <x-Inputs.file-field type="file" label="Image" name="image" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group mb-3 checkbox-checked">
                                    <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-Backend.form-common-footer-buttons />
            </div>
        </div>
    </form>
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
    $(document).ready(function() {

        var cId = $('#cCity').data('id');

        if (cId && cId != '' && cId != 'undefined') {

            $('#cCity option:eq(0)').remove();

            $.ajax({
                url: '/cityname/' + cId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.cities) {
                        $.each(data.cities, function(id, name) {
                            $('#cCity').append('<option value="' + id + '">' +
                                name + '</option>');
                        });
                    } else {
                        $('#cCity').append('<option value="">No Records Found</option>')
                    }
                }
            })

        }

        var pId = $('#pCity').data('id');

        if (pId && pId != '' && pId != 'undefined') {

            $('#pCity option:eq(0)').remove();

            $.ajax({
                url: '/cityname/' + pId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.cities) {
                        $.each(data.cities, function(id, name) {
                            $('#pCity').append('<option value="' + id + '">' +
                                name + '</option>');
                        });
                    } else {
                        $('#pCity').append('<option value="">No Records Found</option>')
                    }
                }
            })

        }
        // state city dropdown
        function updateCities(stateId, selectedId) {

            $('#' + selectedId).html('<option value="">Select</option>');

            $.ajax({
                url: '/cities/' + stateId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.cities) {
                        $.each(data.cities, function(id, name) {
                            $('#' + selectedId).append('<option value="' + id + '">' +
                                name + '</option>');
                        });
                    } else {
                        $('#' + selectedId).append('<option value="">No Records Found</option>')
                    }
                },
            })

        }

        // current state city
        $('#cState').on('change', function() {
            var stateId = $(this).val();
            if (stateId) {
                updateCities(stateId, 'cCity');
            } else {
                $('#cCity').html('<option value="">Select</option>');
            }
        });

        // permanent state city
        $('#pState').on('change', function() {
            var stateId = $(this).val();
            if (stateId) {
                updateCities(stateId, 'pCity');
            } else {
                $('#pCity').html('<option value="">Select</option>');
            }
        });

    })
</script>