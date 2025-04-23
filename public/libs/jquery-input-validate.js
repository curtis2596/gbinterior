/* 
 * @author     Hardeep
 */

/**
 * ^ CHECK FROM START
 * * CHECK EACH CHARACTER
 * g CHECK WHOLE STRING
 */
var regex = {
    int: {
        exact: /^-?[\d]*$/,
        partial: /-?[\d]/
    },
    float: {
        exact: /^-?\d+\.?\d{0,9}$/,
        partial: /-?\d+\.?\d{0,9}/
    },
    alpha: {
        exact: /^[A-Za-z]*$/g,
        partial: /[A-Za-z]/g
    },
    alphaWithSpace: {
        exact: /^[A-Za-z\s]*$/g,
        partial: /[A-Za-z\s]/g
    },
    alphaNumeric: {
        exact: /^[A-Za-z0-9]*$/g,
        partial: /[A-Za-z0-9]/g
    },
    alphaNumericWithSpace: {
        exact: /^[A-Za-z0-9\s]*$/g,
        partial: /[A-Za-z0-9\s]/g
    },
    mobile: {
        exact: /^[0-9]{10}$/g,
        partial: /[0-9]/g
    },
    phone: {
        exact: /^[0-9]{4}[0-9]{7}$/g,
        partial: /[0-9]{4}[0-9]{7}/
    },
    barcode: {
        exact: /^[A-Za-z0-9-_]*$/g,
        partial: /[A-Za-z0-9-_]/g
    },
    box: {
        exact: /^[A-Za-z0-9-_]*$/g,
        partial: /[A-Za-z0-9-_]/g
    }
};

jQuery.fn.extend({
    iValidate: function (type, e, opt) {
        return this.each(function () {
            if (typeof opt != "undefined" && typeof opt.trigger != "undefined") {
                return;
            }

            var v = $(this).val();
            if (!v) {
                v = "";
            }

            if (typeof v == "object") {
                v = v.join(",");
            }

            var msg = $(this).attr("data-" + type + "-msg");
            var from = $(this).attr("data-" + type + "-from");
            var from_val = 0;

            console.log("data-" + type + "-from" + " : " + from + " : "  + typeof from);

            if (from)
            {
                if ($.isNumeric(from))
                {
                    from_val = parseFloat(from);
                }
                else
                {
                    if ($(from).length == 0)
                    {
                        console.error("from : " + from + " : not found");
                        return;
                    }

                    if ($(from).length > 1)
                    {
                        console.error("from : " + from + " : more than 1 found");
                        return;
                    }

                    from_val = parseFloat($(from).first().val());
                }
            }

            var result = validateInputValue(v, type, { from: from_val });

            switch (type) {
                case "not-empty":
                    if (!msg) {
                        msg = "This field is required";
                    }
                    break;

                case "int":

                    if (!msg) {
                        msg = "Number only";
                    }
                    break;

                case "float":
                    if (!msg) {
                        msg = "Decimal only";
                    }
                    break;

                case "alpha":
                    if (!msg) {
                        msg = "Alphabets only";
                    }
                    break;

                case "alphaWithSpace":
                    if (!msg) {
                        msg = "Alphabets only with space";
                    }
                    break;

                case "alphaNumeric":
                    if (!msg) {
                        msg = "Alphabets and numeric only";
                    }
                    break;

                case "alphaNumericWithSpace":
                    if (!msg) {
                        msg = "Alphabets and numeric only with space";
                    }
                    break;

                case "mobile":
                    if (!msg) {
                        msg = "Invalid Mobile";
                    }
                    break;

                case "phone":
                    if (!msg) {
                        msg = "Invalid Phone";
                    }
                    break;

                case "postive-only":
                    if (!msg) {
                        msg = "Postive Only";
                    }
                    break;

                case "barcode":
                    if (!msg) {
                        msg = "Invalid Barcode or Qrcode";
                    }
                    break;

                case "box":
                    if (!msg) {
                        msg = "Only Alphabets, number, (-), (_) allowed";
                    }
                    break;

                case "less-than":
                    if (!msg) {
                        msg = "Enter Less than from " + from_val;
                    }
                case "less-than-equal":
                    if (!msg) {
                        msg = "Enter Less than or equal to " + from_val;
                    }
                case "more-than":
                    if (!msg) {
                        msg = "Enter More than from " + from_val;
                    }
                case "more-than-equal":
                    if (!msg) {
                        msg = "Enter More than or equal to " + from_val;
                    }

                    if (result === false) {
                        $(this).val("");
                    }

                    break;
            }


            if (result === false && typeof regex[type] != "undefined") {
                console.log(v);
                var v_list = v.match(regex[type]["partial"]);
                console.log(v_list);
                if (v_list) {
                    $(this).val(v_list.join(""));
                } else {
                    $(this).val("");
                }
            }

            var $span = $(this).parent().find("." + type + "-error-message");

            if ($span.length == 0) {
                $(this).parent().append('<span class="' + type + '-error-message error-message"></span>');
                $span = $(this).parent().find("." + type + "-error-message");
            }

            console.log(type + " " + result);

            if (result) {
                $span.html("").hide();
            } else {
                $span.html("&#9679 " + msg).show();
            }

        });
    },
});


function validateInputValue(v, type, opt) {
    if (!v) {
        v = "";
    }

    if (typeof v == "object" && v) {
        v = v.join(",");
    }

    switch (type) {
        default: 
            var v_list = v.match(regex[type]["exact"]);
            return v_list !== null;
            break;
            
        case "int":
        case "float":
            if (v == "-")
            {
                return true;
            }			
            var v_list = v.match(regex[type]["exact"]);
            return v_list !== null;
            break;

        case "not-empty":
            return v.trim().length > 0;
            break;

        case "postive-only":
            return parseFloat(v.trim()) > 0;
            break;

        case "less-than":
        case "less-than-equal":
        case "more-than":
        case "more-than-equal":

            if (typeof opt == "undefined" || typeof opt["from"] == "undefined") {
                console.error("from not found in opt");
            }

            var limit = 0;

            if ($.isNumeric(opt["from"])) {
                limit = opt["from"];
            } else {
                console.error("opt.from should be numeric");
                return false;
            }

            var result = true;

            var val = v.length > 0 ? parseFloat(v) : 0;
            switch (type) {
                case "less-than":
                    result = val < limit;
                    break;

                case "less-than-equal":
                    result = val <= limit;
                    break;

                case "more-than":
                    result = val > limit;
                    break;

                case "more-than-equal":
                    result = val >= limit;
                    break;
            }

            return result;
            break;
    }
}

var validate_rules = {
    blur: {
        "validate-not-empty": "not-empty",
        "validate-less-than": "less-than",
        "validate-less-than-equal": "less-than-equal",
        "validate-more-than": "more-than",
        "validate-more-than-equal": "more-than-equal",
        "validate-postive-only": "postive-only",
    },
    keyup: {
        "validate-int": "int",
        "validate-float": "float",
        "validate-alphabet": "alphabet",
        "validate-mobile": "mobile",
        "validate-phone": "phone",
        "validate-alpha-numeric": "alphaNumeric",
        "validate-alpha-with-space": "alphaWithSpace",
        "validate-barcode": "barcode",
        "validate-box": "box",
    },
    change: {
        "validate-not-empty": "not-empty",
    },
};

$(document).ready(function () {
    function event_apply(event, type, selector) {
        $(document).on(event, selector, function (e, opt) {
            $(this).iValidate(type, e, opt);
        });
    }

    for (var cls in validate_rules.blur) {
        event_apply("blur", validate_rules.blur[cls], "input." + cls);
    }

    for (var cls in validate_rules.keyup) {
        event_apply("input propertychange paste", validate_rules.keyup[cls], "input." + cls);
    }

    for (var cls in validate_rules.change) {
        event_apply("change", validate_rules.change[cls], "select." + cls);
    }
});

$(document).on("submit", "form.i-validate", function () {
    var _form = $(this);

    var result = true;

    _form.find(".error-message").remove();

    for (var group in validate_rules) {
        var rules = validate_rules[group];
        for (var cls in rules) {
            var type = rules[cls];
            var selector = "input." + cls;

            if (group == "change") {
                selector = "select." + cls;
            }

            $(this).find(selector).each(function () {
                var v = $(this).val();
                var from = $(this).data(type + "-from");

                var valid = validateInputValue(v, type, { from: from });

                if (!valid) {
                    result = false;
                    $(this).validate(type);

                    if (!result) {
                        var error = _form.find(".error-message:first");

                        if (error.length > 0) {
                            $('html,body').animate({
                                scrollTop: error.top
                            }, 'slow');
                        }
                    }

                    
                }
            });
        }

        return result;
    }
});

function validate_required_group(inputs) {
    var result = false;
    $(inputs).each(function () {
        if ($(this).val().length > 0) {
            result = true;
        }
    });

    return result;
}