$(document).ready(function() {
    $(".go_back").click(function(){
        window.history.back();
    });
});


$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
    cache: false,
});

$(document).ajaxError(function (event, xhr, settings, errorString) {
    if (xhr.status == 403) {
        $.events.onAjaxError(errorString, "Session is expired. Please Login");
    } else if (
        typeof xhr.responseText == "string" &&
        xhr.responseText.length > 0
    ) {
        $.events.onAjaxError(errorString, xhr.responseText, {
            url: settings.url,
        });
    }
});