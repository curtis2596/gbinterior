jQuery.fn.extend({
    ajaxFileUpload: function (opt)
    {
        var feature = "sr-ajax-file-upload";
        
        if (typeof opt["validator"] == "undefined")
        {
            console.error(feature + " : validator should be pass in options");
            return;
        }
        
        var validator = opt["validator"];
        
        if (!validator instanceof $.sr.file.validator)
        {
            console.error(feature + " : validator should be instance of $.sr.file.validator");
            return;
        }
        
        if (typeof opt["onSuccess"] != "function")
        {
            console.error(feature + " : onSuccess function should be pass in options");
            return;
        }
        
        var settings = $.extend({
            onError : function (msg)
            {
                $.sr.error.msg(msg);
            },
            progress : function(sr_file_block, e)
            {
                var percent = Math.round((e.loaded / e.total) * 100);
                sr_file_block.find(".progress-bar").css("width", percent + "%");
                sr_file_block.find(".progress-bar .sr-only").html(percent + "%");
                sr_file_block.find(".progress-status").html("Sent : " + niceBytes(e.loaded));
            },
            beforeUpload : function(fileObj, xhr, progress_block)
            {
                return true;
            },
        }, opt);
        
        var events = {
            onFileSelect : feature + ".onFileSelect"
        }
        
        function attachEvents(ajax, _section)
        {
            ajax.upload.addEventListener("progress", function(e)
            {
                var percent = Math.round((e.loaded / e.total) * 100);
                _section.find(".progress-bar").css("width", percent + "%");
                _section.find(".progress-bar .sr-only").html(percent + "%");
                _section.find(".progress-status").html("Sent : " + $.sr.niceBytes(e.loaded));

            }, false);

            ajax.upload.addEventListener("error", function(e)
            {
                var status = _section.find(".status");
                status.html("Upload Failed");
                console.error(e);
            }, false);

            ajax.upload.addEventListener("abort", function(e)
            {
                console.log("Upload Aborted");
            }, false);

            ajax.onreadystatechange = function() 
            {
                if (ajax.readyState === 4) 
                {
                    if (_section.length == 0 || ajax.responseText.trim().length == 0)
                    {
                        return;
                    }

                    var status = _section.find(".status");

                    try
                    {
                        var response = JSON.parse(ajax.responseText);
                    }
                    catch(e)
                    {
                        $.sr.error.detail("Error in uploading" , ajax.responseText);
                        status.html("Error");
                        return;
                    }

                    if (response["status"] == "1")
                    {
                        _section.find(".abort").hide();
                        status.addClass("alert alert-success");
                        status.html("Success");

                        settings.onSuccess(response["data"]);
                    }
                    else
                    {
                        status.addClass("alert alert-danger");
                        status.html(response["msg"]);
                    }
                }
            }
        }
        
        this.each(function () 
        {
            var output_target = $(this).data(feature + "-output-target");
            
            if (!output_target)
            {
                var parent = $(this).parent();
                
                if ( parent.children("." + feature + "-output-target").length == 0 )
                {
                    parent.append('<div class="' + feature + '-output-target"></div>');
                }
                
                output_target = parent.children("." + feature + "-output-target").first();
            }
            
            if ($(output_target).length == 0)
            {
                console.error(feature + ": output target not found");
                return;
            }
            
            var display = $(this).data(feature + "-display");
            
            if (display != "table" && display != "div")
            {
                display = "table";
            }
            
            $(this).on(events.onFileSelect, function()
            {
                var xhrs = [];
                
                var files = $(this).prop("files");
                
                for (var i = 0; i < files.length; i++)
                {
                    len++;

                    var file = files[i]; 

                    var filename = file.name.length > 60 ? file.name.substr(0, 50) + "..." : file.name;

                    var x = xhrs.length;
                    x = x ? x - 1 : 0;
                    
                    if (display == "table")
                    {
                        if ($(output_target).find("table." + feature).length == 0)
                        {
                            var html = '<table class="' + feature + '">';
                                html += '<thead>';
                                    html += '<tr>';
                                        html += '<th>#</th>';
                                        html += '<th>File</th>';
                                        html += '<th>Total Size</th>';
                                        html += '<th>Status</th>';
                                    html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';
                            html += '</table>';

                            $(output_target).html(html);
                        }

                        var len = $(output_target).find("table." + feature).children("tbody").children("tr").length + 1;
                        
                        var html = '<tr class="sr-ajax-file-upload-block">';
                            html += '<td>';
                                html += len + ' <span class="abort" data-xhr-index="' + x + '"><i class="fa fa-times-circle"></i></span>';
                            html += '</td>';
                            html += '<td>';
                                html += '<div class="filename">' + filename + '</div>';
                                html += '<div class="progress">';
                                    html += '<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:0%">';
                                        html += '<span class="sr-only">0% Complete</span>'
                                    html += '</div>';                                
                                html += '</div>';
                                html += '<div class="progress-status"></div>';
                            html += '</td>';
                            html += '<td><span class="total-size">' + $.sr.niceBytes(file.size) + '</span></td>';
                            html += '</td>';
                            html += '<td class="status"></td>';
                        html += '</tr>';

                        $(output_target).find("tbody").append(html);
                    }
                    else
                    {
                        var html = '<div class="sr-ajax-file-upload-block">';
                            html += '<div class="sr-ajax-file-upload-file-info">';
                                html += '<span class="filename">' + filename + '</span>';
                                html += '<span class="total-size"><b>Total : ' + $.sr.niceBytes(file.size) +  '</b>';
                                html += '<span class="abort" data-xhr-index="' + x + '"><i class="fa fa-times-circle"></i></span></span>';
                            html += '</div>';                            
                            html += '<div class="progress">';
                                html += '<div class="progress-bar  progress-bar-info" style="width: 0%"></div>';
                            html += '</div>';
                            html += '<div class="progress-status"></div>';
                            html += '<div class="status"></div>';
                        html += '</div>';
                        
                        $(output_target).append(html);
                    }

                    var progress_block = $(output_target).find(".sr-ajax-file-upload-block").last();

                    var validate_error = "";

                    try
                    {
                        validator.validate(file);
                    }
                    catch(e)
                    {
                        validate_error = e;
                    }
                    
                    if (validate_error.length > 0)
                    {
                        var status = progress_block.find(".status");
                        status.html(validate_error).addClass("alert alert-danger");
                    }
                    else
                    {
                        var ajax = new XMLHttpRequest();
                        attachEvents(ajax, progress_block);
                        ajax.open("POST", opt.url);

                        xhrs.push(ajax);

                        if (settings.beforeUpload(file, ajax, progress_block))
                        {
                            var formdata = new FormData();
                            formdata.append("file", file);
                            ajax.send(formdata);
                        }
                    }
                }
                
                $(output_target).find(".abort").click(function()
                {
                    var i = $(this).data("xhr-index");                    
                    
                    if ( typeof xhrs[i] != "undefined" )
                    {
                        xhrs[i].abort();
                        xhrs.splice(i, 1);
                    }
                    
                    $(this).closest(".sr-ajax-file-upload-block").remove();
                });
            });
            
            $(this).change(function()
            {
                $(this).trigger(events.onFileSelect);
            });
        });
    }
});

$.sr.file = {
    validator : class
    {
        constructor(size, exts) 
        {
            this.size = size;
            this.exts = exts;
        }
        
        validate (file)
        {
            if ( file.size > this.size )
            {
                throw "File Size should be less than " + $.sr.niceBytes(this.size);
            }
            
            var arr = file.name.split(".");
            
            if (arr.length <= 1)
            {
                throw "File extension not found";
            }
            
            var ext = arr[1].trim();
            
            if (typeof this.exts != "undefined" && typeof this.exts != "array")
            {
                if ( $.inArray(ext, this.exts) === -1)
                {
                    throw "Invalid File type : " + ext;
                }
            }
            
            return true;
        }
    },
};
