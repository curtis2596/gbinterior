function niceBytes(bytes, i) {
	var list = ["B", "KB", "MB", "GB", "TB"];

	if (typeof i == "undefined") {
		i = 0;
	}

	var temp = bytes / 1024;

	if (temp > 1024) {
		return niceBytes(temp, i + 1);
	}

	if (temp < 1) {
		return bytes.toFixed(1) + " " + list[i];
	} else {
		return temp.toFixed(1) + " " + list[i + 1];
	}
}

function niceNumbers($number, $count = 0)
{
	if ($number > 1000) {
		return niceNumbers(Math.round($number / 1000), $count + 1);
	}

	$types = ["", "K", "M", "T"];

	return $number + " " + $types[$count];
}

function ajaxHandleResponse(url, response, callback) {

    var responseJson = {};
    if (typeof response == "object") {
        responseJson = response;
    } else {
        try {

            if (typeof (response) == "string") {
                response = response.trim();

                if (response.length == 0) {
                    $.events.onAjaxError("JSON Parse Error", "Empty Response", {
                        url: url,
                    });

                    return false;
                }

                var responseJson = JSON.parse(response);
            }
        } catch (e) {
            $.events.onAjaxError("JSON Parse Error", response, {
                url: url,
            });
            return false;
        }
    }

    if (typeof responseJson["status"] == "undefined") {
        $.events.onAjaxError("Missing", "Response JSON Should have status", {
            url: url,
        });
        return;
    }

    if (responseJson["status"] == "1" || responseJson["status"] == true) {
        if (typeof callback == "function") {
            callback(responseJson);
        }
    } else if (typeof responseJson["msg"] != "undefined") {
        $.events.onUserError(responseJson["msg"]);
    } else {
        $.events.onAjaxError("Missing", "Response JSON Should have msg", {
            url: url,
        });
    }
}



function ajaxGetJson(url, callback) {
    $.loader.init();
    $.loader.setInfo("Loading...").show();

    $.get(url, function (response) {

        $.loader.hide();

        ajaxHandleResponse(url, response, callback);

    }).fail(function (xhr, status, title) {
        $.loader.hide();
    });
}

function ajaxPostJson(url, data, callback) {
    $.loader.init();
    $.loader.setInfo("Loading...").show();

    $.post(url, data, function (response) {

        $.loader.hide();

        ajaxHandleResponse(url, response, callback);

    }).fail(function (xhr, status, title) {
        $.loader.hide();
    });
}


function form_errors(form, errors) {
    var error_input_found = false;

    form.find(".error-message").remove();

    for (var field in errors) {
        var errs = errors[field];
        var key = "[name='" + field + "']";
        var input = form.find("input" + key);
        var select = form.find("select" + key);

        if (input.length > 0) {
            error_input_found = true;
            for (var e in errs) {
                input.parent().append('<span class="error-message">' + errs[e] + '<span>');
            }
        }

        if (select.length > 0) {
            error_input_found = true;
            for (var e in errs) {
                select.parent().append('<span class="error-message">' + errs[e] + '<span>');
            }
        }

    }

    return error_input_found;
}

function confirmDialog(text, onYes) {
    Swal.fire({
        title: "Are you sure?",
        text: text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: constants.swal.button.confirm_color,
        cancelButtonColor: constants.swal.button.cancel_color,
    }).then(function (e) {
        if (e.value) {
            if (typeof onYes == "function") {
                onYes();
            }
        }
    });
}

function form_input_toggle_mandatory(element, is_show)
{
    if (is_show)
    {
        if ( element.closest(".form-group").find(".form-label .mandatory").length == 0)
        {
            element.closest(".form-group").find(".form-label").append('<span class="mandatory">*</span>');
        }
        else
        {
            element.closest(".form-group").find(".form-label .mandatory").show();
        }

        element.attr("required", "true");
    }
    else
    {
        element.closest(".form-group").find(".form-label .mandatory").hide();
        element.removeAttr("required");
    }
}

function form_check_unique_list(cls)
{
    var result = true;
    var list = [];

    $(cls).each(function(){
        var v = $(this).val();
        if (v)
        {
            if (list.includes(v))
            {
                result = false;
            }

            list.push(v);
        }
    });

    // console.log({"form_check_unique_list" : list});

    return result;
}

function str_convert_space_to_hyphine(str) {
    return str.replace(/\s+/g, '-');  // Replace multiple spaces with a single hyphen
}

function str_trim_hyphine(str) {
    return str.replace(/^-+|-+$/g, ''); 
}