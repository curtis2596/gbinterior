/**
 * @author : Hardeep
 * this file only for backend
 */

$(document).ready(function () {
    $.loader.init();

    $("form").find("div.pristine-error").parents(".form-group").addClass("has-danger");

    $("input[type='checkbox'].chk-select-all").chkSelectAll();

    $(".css-toggler").cssClassToggle();

    $(".ajax-load").ajaxLoad();

    $(".table-export-csv").srTableCSVExport();

    $(".select2").select2({
        placeHolder: "Please Select",
        theme: "bootstrap-5",
    });

    $(".fancybox").fancybox();

    $(".date-picker").flatpickr({
        dateFormat: "d-M-Y",
    });

    $(".date-time-picker").flatpickr({
        enableTime: true,
        time_24hr: false,
        dateFormat: "d-M-Y h:i K",
    });


    $('.time-picker').flatpickr({
        noCalendar: true,
        enableTime: true,
        dateFormat: 'h:i K'
    });

    // flatpickr(".date-month-picker", {
    //     disableMobile: "true",
    //     plugins: [
    //         new monthSelectPlugin({
    //             shorthand: true,
    //             dateFormat: "m/Y",
    //             altFormat: "F Y",
    //             theme: "material_blue"
    //         })
    //     ]
    // });

    $(".i-data-table").idataTable();



    /** -------------------------------------------------------- */



    $('input.invalid-char').on('keypress', function (event) {
        var regex = new RegExp("^[a-zA-Z0-9~!@#$%^*()<>{}_.,/\+\-\\\\]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        key = key.trim();

        var error_span = $(this).parent().find(".error-message");
        if (error_span.length == 0) {
            $(this).parent().append('<span class="error-message">&#9679 Invalid Character</span>');
            var error_span = $(this).parent().find(".error-message");
        }

        if (key && !regex.test(key)) {
            event.preventDefault();
            error_span.show();
            return false;
        }

        error_span.hide();
    });

    $(document).on("submit", "form.summary-delete-form", function () {
        var _form = $(this);

        var is_confirm = _form.attr("data-confirm");

        if (!is_confirm) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: constants.swal.button.confirm_color,
                cancelButtonColor: constants.swal.button.cancel_color,
                confirmButtonText: "Yes, delete it!"
            }).then(function (e) {
                if (e.value) {
                    _form.attr("data-confirm", 1);
                    _form.trigger("submit");
                }
            });

            return false;
        }
    });


    $(document).on("submit", "form.summary_search", function () {
        $(this).ajaxSubmit({
            target: '#index_table',
            beforeSubmit: function (formData, jqForm, options) {
                $.loader.show();
            },
            success: function (responseText, statusText, xhr, $form) {
                $.loader.hide();
            },
            error: function () {
                $.loader.hide();
            }
        });

        return false;
    });

    $(document).on("click", "form .clear_form_search_conditions", function () {
        var _form = $(this).closest("form");

        _form.clearForm();

        _form.find("input[name='is_sort_clear']").val(1);

        var v = _form.find(".pagination_limit:first").find("option:first").val();
        _form.find(".pagination_limit").val(v);

        _form.trigger("submit");
    });

    $(document).on("change", "form.summary_search select.pagination_limit", function () {
        var _form = $(this).closest("form");
        _form.trigger("submit");
    });

    $("#index_table").on("click", ".pagination a.page-link, a.sortable", function () {

        var href = $(this).attr("href");
        $.loader.show();

        $("#index_table").load(href, function () {
            $.loader.hide();
        });

        return false;
    });

    $(document).on("click", "a.activate", function () {

        var _this = $(this).parent();
        var href = $(this).attr("href");

        ajaxGetJson(href, function (response) {
            var html = '<span class="badge bg-success">Active</span>';
            html += '<br/>'
            html += '<a class="de_activate" href="' + response['url'] + '">De-Activate</a>';
            _this.html(html);
        });

        return false;
    });

    $(document).on("click", "a.de_activate", function () {

        var _this = $(this).parent();
        var href = $(this).attr("href");

        ajaxGetJson(href, function (response) {
            var html = '<span class="badge bg-danger">De-Active</span>';
            html += '<br/>'
            html += '<a class="activate" href="' + response['url'] + '">Activate</a>';
            _this.html(html);
        });

        return false;
    });

    $(document).on("click", "a.confirm", function () {

        var _this = $(this).parent();
        var href = $(this).attr("href");
        var msg = $(this).data("msg");
        msg = msg ? msg : "Are You Sure?";

        confirmDialog(msg, function () {
            window.location.href = href;
        })

        return false;
    });
});


