@extends($layout)
@section('content')

<style>    
    .blue{
        background-color: #19bbd2;
        color : #FFF;
    }
    .purple
    { 
        background-color: #8f70e7; 
        color: #FFF;
    }
    .magenta{ 
        background-color: #ae379b; 
        color: #FFF;
    }

    .yellow {
        background-color: #fecb4b;
        color: #FFF;
    }

    
    .counter {
        padding: 0;
        box-shadow: 4px 4px 10px 0px rgba(0, 0, 0, 0.5);
    }

    .counter ol {
        list-style: none;
        padding-left: 1px;
    }

    .counter ol li
    {

    }

    .counter .card-title{
        padding: 6px;
        text-align: center;
    }

    .counter .card-body{
        padding: 4px;
    }

    .dashboard-table thead
    {
        position: sticky;
        background-color: #19bbd2;
        color: #FFF;
    }

    .dashboard-table tbody
    {
        height: 200px; /* Adjust to your desired height */
        overflow-y: scroll; /* Enables vertical scrolling for the tbody */
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4>{{ isset($page_title) ? $page_title : "Please set page_title variable"; }}</h4>

            <div class="page-title-right" style="width : 30vw">
                <x-inputs.drop-down id="duration_type" name="duration_type" label="Duration"
                :list="$duration_type_list" class="form-control select2" :pleaseSelect="false" />
            </div>

        </div>
    </div>
</div>

<div id="counters" class="mt-4 mb-4">    
</div>

<script type="text/javascript">
    $(function(){
        $("#duration_type").change(function(){
            var v = $(this).val();
            if (v != "")
            {
                $.loader.init().show();
                $("#counters").load("/dashboard-ajax_admin_role_counters/" + v, function(){
                    $.loader.hide();
                });
            }
        }).trigger("change", {pageLoad : true});
    });
</script>

@endsection