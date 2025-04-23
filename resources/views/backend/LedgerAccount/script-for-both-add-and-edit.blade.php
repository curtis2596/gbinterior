<?php

use App\Models\LedgerCategory;
?>

<script>
    $(function() {
        $("#ledger_category_id").change(function(){

            var v = $(this).val();

            $(".bank_inputs").hide();
            $(".bank_inputs input, .bank_inputs select").removeAttr("required");
            $(".bank_inputs .form-label .mandatory").hide();

            if (v)
            {
                ajaxGetJson("/ledger-category_ajax_get/" + v, function(response){
                    var ledger_category = response['data'];
                    
                    if (ledger_category['code'] == '<?= LedgerCategory::CODE_bank ?>')
                    {
                        $(".bank_inputs").show();
                        $(".bank_inputs input, .bank_inputs select").attr("required", "true");

                        $(".bank_inputs .form-label").each(function(){
                            if ($(this).find(".mandatory").length == 0)
                            {  
                                $(this).append('<span class="mandatory">*</span>');
                            }
                            else
                            {
                                $(this).find(".mandatory").show();
                            }
                        });
                    }
                });
            }
        });

        $("#ledger_category_id").trigger("change");

        $("#get_bank_detail_from_ifsc_code").click(function()
        {
            var code = $("#bank_branch_ifsc").val();
            
            if (!code)
            {
                $.events.onUserWarning("Enter Bank IFSC Code");
                return;
            }
            
            $.loader.init();
            $.loader.setInfo("Fetching Bank IFSC Details....").show();
            $.get("/public/ajax_get_bank_detail_from_ifsc_code/" + code, function(response) 
            {
                $.loader.hide();
                try
                {
                    if (typeof response == "string")
                    {
                        response = JSON.parse(response);
                    }
                }
                catch(e)
                {
                    bootbox.alert(response);
                    return;
                }
                
                if (response["status"] == "1")
                {
                    var data = response["data"];
                    console.log(data);

                    $("#bank_name").val(data['BANK']);
                    $("#bank_branch_name").val(data['BRANCH']);
                    $("#bank_branch_address").val(data['ADDRESS']);
                }
                else
                {
                    bootbox.alert(response["msg"]);
                    return;
                }
            }).fail(function(){
                $.loader.hide();
            });
        });
    });
</script>