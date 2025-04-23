var DateUtil = {
    viewDateFormat: "d-M-Y",
    sqlDateFormat: "Y-m-d",

    viewTimeFormat: "h:i a",
    sqlTimeFormat: "H:i:s",

    viewDateTimeFormat: "d-M-Y h:i a",
    sqlDateTimeFormat: "Y-m-d H:i:s",

    monthList: [
        "Jan", "Feb", "Mar",
        "Apr", "May", "Jun",
        "Jul", "Aug", "Sep",
        "Oct", "Nov", "Dec"
    ],
    day: "day",
    month: "month",
    year: "year",
    hour: "hour",
    minute: "minute",
    second: "second",
    getSupportedFormats: function() {
        return [
            'd-M-Y',
            'd-m-Y',

            'Y-M-d',
            'Y-m-d',

            'H:i:s',
            'h:i:s a',

            'H:i',
            'h:i a',
        ];
    },
    getFormattedDateTime: function(obj, format) {
        var date = null;
        if (typeof(obj) == "object" && obj instanceof Date) {
            date = obj;
        } else {
            date = this.getDateTimeObj(obj);

            if (!date) {
                console.error("Invalid Date : " + date);
                return null;
            }
        }

        var gmt_diff = date.getTimezoneOffset(); //return diffrence in mintues;

        if (gmt_diff > 0) {
            date.setTime(date.getTime() - (gmt_diff * 60000));
        }

        var d = date.getDate();
        var m = date.getMonth();
        var M = this.monthList[m];
        var Y = date.getFullYear();

        var H = date.getHours();
        var i = date.getMinutes();
        var s = date.getSeconds();

        var am_pm = "";
        var h = 0;
        if (H > 12) {
            am_pm = "pm";
            h = H - 12;
        } else {
            am_pm = "am";
            h = H;
        }

        var date_str = format;

        d = d.toString();
        d = d.padStart(2, '0');
        date_str = date_str.replace("d", d);

        m = m.toString();
        m = m.padStart(2, '0');
        date_str = date_str.replace("m", m);

        Y = Y.toString();
        Y = Y.padStart(2, '19');
        date_str = date_str.replace("Y", Y);

        H = H.toString();
        H = H.padStart(2, '0');
        date_str = date_str.replace("H", H);

        h = h.toString();
        h = h.padStart(2, '0');
        date_str = date_str.replace("h", h);

        i = i.toString();
        i = i.padStart(2, '0');
        date_str = date_str.replace("i", i);

        s = s.toString();
        s = s.padStart(2, '0');
        date_str = date_str.replace("s", s);

        date_str = date_str.replace("a", am_pm);

        //month name can conflict with format
        date_str = date_str.replace("M", M);

        return date_str;
    },
    getDateTimeObj: function(str) {
        if (str instanceof Date) {
            return str;
        }

        var Y, m, d, H, i, s;

        try {
            if (typeof str != "string") {
                throw "getDateTimeObj() : input should be string";
            }

            str = str.trim();
            str = str.replace(/\s\s+/g, ' ');

            var parts = str.split(" ");

            if (parts.length > 3) {
                throw "Input have extra parts after split by space";
            }

            var result = this.strToDateParts(parts[0]);

            d = result['d'];
            m = result['m'];
            Y = result['Y'];

            H = i = s = 0;

            if (parts.length == 3) {
                parts[1] += " " + parts[2];
                parts.splice(2, 1);
            }

            if (parts.length == 2) {
                var result = this.strToTimeParts(parts[1]);

                H = result['H'];
                i = result['i'];
                s = result['s'];
            }
        } catch (e) {
            console.error("Date-Util Error : " + e.toString());
            return null;
        }

        console.log({
            "Y": Y,
            "m": m,
            "d": d,
            "H": H,
            "i": i,
            "s": s,
        });

        var date = new Date(Y, m, d, H, i, s);

        if (date.toString() != "Invalid Date") {
            return date;
        }

        return null;
    },
    getTimeObj: function(str) {
        var H, i, s;

        try {
            var parts = str.split(" ");

            if (parts.length == 2) {
                parts = parts[1];
            } else {
                parts = parts[1];
            }

            var result = this.strToTimeParts(parts);

            H = result['H'];
            i = result['i'];
            s = result['s'];
        } catch (e) {
            console.error("Date-Util Error : " + e.toString());
            return null;
        }

        console.log({
            "H": H,
            "i": i,
            "s": s,
        });

        var date = new Date(0, 0, 0, H, i, s);

        if (date.toString() != "Invalid Date") {
            return date;
        }

        return null;
    },

    strToDateParts(str) {
        var Y, m, d;

        str = str.trim();
        var hyphin_parts = str.split("-");

        if (hyphin_parts.length != 3) {
            throw "Date should have 3 parts after split by hyphin (-)";
        }

        for (var i in hyphin_parts) {
            hyphin_parts[i] = hyphin_parts[i].trim();
        }

        var day_part = "";
        if (hyphin_parts[0].length == 4) {
            Y = parseInt(hyphin_parts[0]);
            day_part = hyphin_parts[2];
        } else if (hyphin_parts[2].length == 4) {
            day_part = hyphin_parts[0];
            Y = parseInt(hyphin_parts[2]);
        } else {
            throw "Date first or last part should be year";
        }

        if (day_part.length == 0) {
            throw "Day should be 1 or 2 characters long";
        } else if (day_part.length > 2) {
            throw "Day has extra characters";
        }

        d = parseInt(day_part);

        if (hyphin_parts[1].length == 3) {
            var M = hyphin_parts[1].toLowerCase();

            m = -1;
            for (var i in this.monthList) {
                var month = this.monthList[i].toLowerCase();

                if (M == month) {
                    m = i;
                }
            }

            if (m == -1) {
                throw "month Name : " + M + " is not valid";
            }
        } else if (hyphin_parts[1].length == 2) {
            m = parseInt(hyphin_parts[1]);

            if (typeof m == "number") {
                m = m - 1;
            } else {
                throw "month should be numeric";
            }
        } else {
            throw "Date centeral part should be month";
        }

        if (d > 31) {
            throw "Day is more than 31";
        }

        if (m > 11) {
            throw "Month index is more than 11";
        }

        if (Y < 1900) {
            throw "Year is less than 1900";
        }

        return {
            "Y": Y,
            "m": m,
            "d": d,
        };
    },

    strToTimeParts(str) {
        var H, i, s;

        str = str.trim();
        var space_parts = str.split(" ");

        if (space_parts.length > 2) {
            throw "Time has extra space";
        }

        for (var j in space_parts) {
            space_parts[j] = space_parts[j].trim();
        }

        var first_parts = space_parts[0].split(":");

        for (var j in first_parts) {
            first_parts[j] = first_parts[j].trim();
        }

        if (first_parts.length < 2) {
            throw "Time should have 2 characters seprated by colon (:)";
        } else if (first_parts.length > 3) {
            throw "Time has extra colon(:)";
        }

        if (first_parts[0].length == 0) {
            throw "Hour should be 1 or 2 characters long";
        } else if (first_parts[0].length > 2) {
            throw "Hour has extra characters";
        }

        if (first_parts[1].length == 0) {
            throw "Minute should be 1 or 2 characters long";
        } else if (first_parts[1].length > 2) {
            throw "Minute has extra characters";
        }

        if (first_parts.length == 3) {
            if (first_parts[2].length == 0) {
                throw "Seconds should be 1 or 2 characters long";
            } else if (first_parts[2].length > 2) {
                throw "Seconds has extra characters";
            }
        }

        var am_pm;

        if (first_parts.length == 2) {
            var h = parseInt(first_parts[0]);
            i = parseInt(first_parts[1]);
            s = 0;
        } else if (first_parts.length == 3) {
            var h = parseInt(first_parts[0]);
            i = parseInt(first_parts[1]);
            s = parseInt(first_parts[2]);
        }

        if (space_parts.length == 2) {
            am_pm = space_parts[1].toLowerCase();

            if (am_pm == "am") {
                if (h > 11) {
                    H = h - 12;
                } else {
                    H = h;
                }
            } else if (am_pm == "pm") {
                if (h <= 11) {
                    H = h + 12;
                } else {
                    H = h;
                }
            } else {
                throw "Invalid AM or PM, found " + am_pm;
            }
        } else {
            H = h;
        }

        if (H > 23) {
            throw "Hour is more than 23";
        }

        if (i > 59) {
            throw "Minute is more than 59";
        }

        if (s > 59) {
            throw "Seconds is more than 59";
        }

        return {
            "H": H,
            "i": i,
            "s": s,
        };
    },

    getDifference: function(first_date, second_date, type, is_float) {

        if (typeof first_date == "string") {
            first_date = this.getDateTimeObj(first_date);
            if (first_date == null) {
                throw "Invalid First Date";
            }
        }

        if (typeof second_date == "string") {
            second_date = this.getDateTimeObj(second_date);

            if (second_date == null) {
                throw "Invalid Second Date";
            }
        }

        var diff = first_date.getTime() - second_date.getTime();

        if (typeof type == "undefined") {
            return diff;
        }

        var diff_seconds = Math.floor(diff / 1000);

        if (type === this.second) {
            return diff_seconds;
        }

        var diff_minutes = diff_seconds / 60;
        var minutes;
        if (is_float) {
            minutes = Math.round(diff_minutes * 100) / 100;
        } else {
            minutes = Math.floor(diff_minutes);
        }

        if (type === this.minute) {
            return minutes;
        }

        var diff_hours = diff_minutes / 60;
        var hours;
        if (is_float) {
            hours = Math.round(diff_hours * 100) / 100;
        } else {
            hours = Math.floor(diff_hours);
        }

        if (type === this.hour) {
            return hours;
        }


        var diff_days = diff_hours / 24;
        var days;
        if (is_float) {
            days = Math.round(diff_days * 100) / 100;
        } else {
            days = Math.floor(diff_days);
        }

        if (type === this.day) {
            return days;
        }


        var diff_months = diff_days / 12;
        var months;
        if (is_float) {
            months = Math.round(diff_months * 100) / 100;
        } else {
            months = Math.floor(diff_months);
        }

        if (type === this.month) {
            return months;
        }

        var diff_years = diff_months / 12;
        var years;
        if (is_float) {
            years = Math.round(diff_years * 100) / 100;
        } else {
            years = Math.floor(diff_years);
        }

        if (type === this.year) {
            return years;
        }

        return null;
    },
    testGetTimeObj() {
        var Y = 2021,
            m = 12,
            d = 31;

        var am_pm_list = ["am", "pm"];
        for (var H = 0; H <= 23; H++) {
            for (var am_pm in ["am", "pm"]) {
                var str = Y + "-" + m + "-" + d + " " + H + ":00 " + am_pm_list[am_pm];

                console.log(str);
                this.getDateTimeObj(str);
            }
        }
    }
};