//Ajax start
$(document).ready(function()
{
    $("form").find("div.pristine-error").parents(".form-group").addClass("has-danger");

    $("input[type='checkbox'].chk-select-all").chkSelectAll();

    $(".select2").select2({
        placeHolder : "Please Select",        
        theme : "bootstrap-5",
    });

    $(".modal-select2").select2({
        placeHolder : "Please Select",        
        theme : "bootstrap-5",
        dropdownParent: $("#modal_form")
    });

    $(".fancybox").fancybox();

    $(".css-toggler").cssClassToggle();

    $(".ajax-load").ajaxLoad();
});