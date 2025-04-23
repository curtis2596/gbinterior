<div class="accordion mb-3" id="ledger-accordion">
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-1">
          <span>
             <i class="fa fa-search text-primary me-1"></i> Account / Stock Ledger
          </span>
        </button>
      </h2>
      <div id="collapse-1" class="accordion-collapse collapse show" data-bs-parent="#ledger-accordion">
        <div class="accordion-body">
          <form id="search">
            <div class="row">
              <div class="col-lg-5">
                <div class="input-group mb-2">
                  <span class="input-group-text">Type</span>
                  <select class="form-control" name="type" required>
                    <option value="">Choose...</option>
                    <option value="account" {{ request('type') == 'account' ? 'selected' : '' }}>Account</option>
                 </select>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="input-group mb-2">
                  <span class="input-group-text">Party</span>
                  <select name="party_id" class="form-control" required>
                    <option value="">Choose...</option>
                    @foreach($parties as $id => $name)
                    <option value="{{ $id }}" {{ $id == request('party_id') ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-lg-1">
                <button class="btn btn-primary"><i class="fa fa-search"></i></button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
   $('#categoryname').on('change', function() {
       var abc =  $(this).find(":selected").val();

          $.ajax({
               url: "/7daysmart/public/getcategory/"+abc,
              type: 'GET',
            success: function(res) {
              //$('#parent').append('<option value="">--Select Sub Category--</option>');
              $.each(res, function (key, val) {
                   $('#parent').append('<option value="'+val.sub_cat_id+'">'+val.sub_cat_name+'</option>');
             });
            }

         });


   });

      $('#parent').on('change', function() {
             var secondValue =  $(this).find(":selected").val();

               $.ajax({
                  url: "/7daysmart/public/getproducts/"+secondValue,
                  type: 'GET',
                  success: function(res) {
                  $('#childcat').empty();
                  $.each(res, function (key, val) {
                  $('#childcat').append('<option value="'+val.sub_cat_id+'">'+val.sub_cat_name+'</option>');
                });
      }

   });

       });


</script> -->
