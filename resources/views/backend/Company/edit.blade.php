@extends($layout)

@section('content')

@include($partial_path . ".page_header")

<form action="{{ route('companies.update', ['company' => $company ]) }}" method="POST"
    enctype="multipart/form-data" class="p-3 bg-white rounded shadow-sm">
    @csrf
    @method('PUT')

    <section class="row">
        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">Company Name</label>
            <input type="text" class="form-control" name="name" value="{{ $company->name }}" required>
        </div>

        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">Phone No.</label>
            <input type="text" class="form-control" name="phone_number" value="{{ $company->phone_number }}" required>
        </div>

        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">GST No.</label>
            <input type="text" class="form-control" name="gst_number" value="{{ $company->gst_number }}">
        </div>

        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">Email Address</label>
            <input type="text" class="form-control" name="email" value="{{ $company->email }}">
        </div>

        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">Website</label>
            <input type="text" class="form-control" name="website" value="{{ $company->website }}">
        </div>


    </section>
    <section class="row mb-3">
        <div class="col-lg-4">
            <label for="" class="form-label">Logo</label>
            <?php $attr_required = $company->logo ? "" : "required"; ?>
            <input type="file" class="form-control" name="logo" <?= $attr_required ?>>
            <div class="text-help">
                Width Should be between 100px to 250px
                <br/>
                Height Should be between 30px to 100px
            </div>
        </div>
        <div class="col-lg-2" style="padding-top: 20px;">
            @if (isset($company->logo))
                <a href="{{ url($company->logo) }}" target="_blank">
                    <img src="{{ url($company->logo) }}" alt="{{ $company->logo }}"
                    class="mb-4"
                    style="width:12rem; height: 4rem; object-fit:contain;">
               </a>
            @endif
        </div>
        <div class="col-lg-4">
            <label for="" class="form-label">Logo For PDF And Print</label>
            <?php $attr_required = $company->logo_for_pdf ? "" : "required"; ?>
            <input type="file" class="form-control" name="logo_for_pdf" <?= $attr_required ?>>
            <div class="text-help">
                Width Should be between 50px to 400px
                <br/>
                Height Should be between 20px to 150px
            </div>
        </div>
        <div class="col-lg-2" style="padding-top: 20px;">
            @if (isset($company->logo_for_pdf))
                <a href="{{ url($company->logo_for_pdf) }}" target="_blank">
                    <img src="{{ url($company->logo_for_pdf) }}" alt="{{ $company->logo }}"
                    class="mb-4"
                    style="width:12rem; height: 4rem; object-fit:contain;">
               </a>
            @endif
        </div>
    </section>


    <h6 class="fw-bold border-bottom pb-2 mb-3">Address</h6>


    <section class="row mb-4">
        <div class="col-lg-12 mb-3">
            <label for="" class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="{{ $company->address }}" required>
        </div>

        <div class="col-md-6 mb-3">
        <x-Inputs.drop-down id="state_id" name="state_id" label="State"
            :list="$state_list" 
            :value="$company->state_id"
            class="form-control select2 cascade" :mandatory="true"
            data-sr-cascade-target="#city_id"
            data-sr-cascade-url="/cities-ajax_get_list/{v}" />
        </div>

        <div class="col-md-6 mb-3">
            <x-Inputs.drop-down id="city_id" name="city_id" label="City"
                :list="[]" data-value="{{ $company->city_id }}"
                class="form-control select2"
                :mandatory="true"
                />
        </div>


    </section>

    <h6 class="fw-bold border-bottom pb-2 mb-3">Bank Information</h6>

    <section class="row">
        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">Bank Name</label>
            <input type="text" class="form-control" name="bank_name" value="{{ $company->bank_name }}">
        </div>

        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">Account Name</label>
            <input type="text" class="form-control" name="account_name" value="{{ $company->account_name }}">
        </div>

        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">IFSC Code</label>
            <input type="text" class="form-control" name="ifsc_code" value="{{ $company->ifsc_code }}">
        </div>

        <div class="col-lg-6 mb-3">
            <label for="" class="form-label">Account No.</label>
            <input type="text" class="form-control" name="account_number" value="{{ $company->account_number }}">
        </div>
    </section>

    <header class="py-2">
        <h6 class="fw-bold">Terms & Conditions</h6>
        <hr>
    </header>

    <div class="mb-3">
        <label for="" class="form-label">Terms & Conditions</label>
        <textarea name="terms" id="terms" cols="30" rows="6" class="form-control">{{ $company->terms ?? old('terms') }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary ">Edit</button>
</form>

@endsection


@push('scripts')

<script>

    function enableSelectize(){
       $('select').selectize({ sortField: 'text' });
    }

   $(document).ready(()=>{

       tinymce.init({
            selector: '[name=terms]',
            height: 420,
            branding: false,
            plugins: 'lists link image paste table fullscreen',
            toolbar: `undo redo | bold italic underline | alignleft
                    aligncenter alignright alignjustify | bullist numlist outdent indent
                    | table |link image | fullscreen`,
        });

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
                var value = dest.attr("data-value");
                if (value) {
                    var v = dest.attr("data-value").split(",");
                    dest.val(v);

                    if (dest.attr("id") == "purchase_order_ids") {
                        after_purchase_list_fetch();
                    }
                }
            },
        }).trigger("change", {
            "pageLoad": true
        });
   });

</script>

@endpush        