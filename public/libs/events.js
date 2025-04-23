$.events = {
    onAjaxError: function (title, responseHtml, extra) {
        var config = {
            title: title,
            message: responseHtml,
            size: "extra-large",
        };

        bootbox.alert(config);
    },
    onUserError: function (html, extra) {
        var config = {
            icon: "error",
            showCloseButton: true,
            html: html,
        };

        if (typeof extra == "object") {
            if (typeof extra.width == "string") {
                config.width = extra.width;
            }
        }

        Swal.fire(config);
    },
    onUserWarning : function(html, extra) {
        var config = {
            icon: "warning",
            showCloseButton: true,
            html: html,
        };

        if (typeof extra == "object") {
            if (typeof extra.width == "string") {
                config.width = extra.width;
            }
        }

        Swal.fire(config);
    }
};