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
        <div class="col-lg-6 mb-3">
            <x-Inputs.text-field name="name" label="Name" placeholder="Enter Name" :value="$model->name ?? old('name')" :mandatory="true" />
        </div>

        <div class="col-lg-6 mb-3">
            <?php
            $list = laravel_constant("balance_types");
            ?>
            <label class="form-label">Opening Balance *</label>
            <div class="input-group opening_balance">
                <x-Inputs.drop-down name="opening_balance_type" label="" :list="$list" value="{{ $party->opening_balance_type ?? '' }}"
                    class="form-control opening_balance_type" />
                <x-Inputs.text-field name="opening_balance" label="" value="{{ $party->opening_balance ?? 0 }}"
                    class="form-control opening_balance validate-float validate-more-than-equal" data-more-than-equal-from="0" />
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <x-Inputs.drop-down name="city_id" label="City"
                :list="$cities"
                :value="$model->city_id ?? old('city_id')"
                class="select2" :mandatory="true" />
            <!-- <label for="" class="form-label">City</label>
            <select name="city_id" id="city" class="form-control">
                @if(isset($party))
                @foreach($cities as $id => $city)
                <option value="{{ $id }}" {{ $party->city_id == $id ? 'selected' : '' }}>
                    {{ $city }}
                </option>
                @endforeach
                @else
                <option selected value="">Choose...</option>
                @foreach($cities as $id => $city)
                <option {{ old('city_id') == $id ? 'selected' : '' }} value="{{ $id }}">
                    {{ $city }}
                </option>
                @endforeach
                @endif
            </select> -->
        </div>

        <div class="col-lg-6 mb-3">
            <x-Inputs.text-field name="address" label="Address" placeholder="Enter address" :value="$model->address ?? old('address')" />
        </div>

        <div class="col-lg-6 mb-3">
            <x-Inputs.text-field name="phone" class="validate-float form-control" label="Phone No." placeholder="Enter Phone No." :value="$model->phone ?? old('phone')" />
        </div>

        <div class="col-lg-6 mb-3">
            <x-Inputs.text-field name="mobile" class="validate-float form-control" label="Mobile No." placeholder="Enter Mobile No." :value="$model->mobile ?? old('mobile')" />
        </div>

        <div class="col-lg-6 mb-3">
            <x-Inputs.text-field name="email" label="Email" placeholder="Enter Email" :value="$model->email ?? old('email')" />
        </div>

        <div class="col-lg-3 mb-3">
            <x-Inputs.text-field name="fax" label="Fax" placeholder="Enter fax" :value="$model->fax ?? old('fax')" />
        </div>

        <div class="col-lg-3 mb-3">
            <x-Inputs.text-field name="tin_number" class="validate-float form-control" label="TIN No." placeholder="Enter TIN No." :value="$model->tin_number ?? old('tin_number')" />
        </div>


        <div class="col-lg-6 mb-3">
            <x-Inputs.drop-down name="category_id" label="Category"
                :list="$categories"
                :value="$model->category_id ?? old('category_id')"
                class="select2" :mandatory="true" />
        </div>

        <div class="col-lg-6 mb-3">
            <x-Inputs.text-field name="url" label="Website (URL)" placeholder="Enter Url" :value="$model->url ?? old('url')" />
        </div>
        <div class="col-lg-6 mb-3">
            <x-Inputs.drop-down name="user_id" label="User"
                :list="$users"
                :value="$model->user_id ?? old('user_id')"
                class="select2" :mandatory="true" />
        </div>
    </div>
    <div class="mb-3">
        <x-Inputs.text-area name="note" label="Note" :value="$model->note ?? old('note')"/>
    </div>
    <div class="form-group mb-3">
        <x-Inputs.checkbox name="is_supplier" label="Supplier" :value="$model->is_supplier ?? false" />
        <x-Inputs.checkbox name="is_customer" label="Customer" :value="$model->is_customer ?? false" />
        <x-Inputs.checkbox name="is_job_worker" label="Job Worker" :value="$model->is_job_worker ?? false" />
        <x-Inputs.checkbox name="is_active" label="Active" :value="$model->is_active ?? false" />
    </div>
    <div class="form-buttons">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>

@push('scripts')

<script>
    $(document).ready(() => {
        $('select').selectize();

        $("#category").change(function() {
            var text = $("#category option:selected").text();

            if (text) {
                text = text.toLocaleLowerCase().trim();
                text = text.replace(/\s+/g, '-');

                if (text == "customer") {
                    $("input[name='is_customer']").prop("checked", true);
                }

                if (text == "supplier") {
                    $("input[name='is_supplier']").prop("checked", true);
                }

                if (text == "job-worker") {
                    $("input[name='is_job_worker']").prop("checked", true);
                }
            }
        });
    });
</script>
@endpush


@endsection