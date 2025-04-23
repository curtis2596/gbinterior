/**
 * @created 08-Oct-2023
 * @author Hardeep
 */

$.loader = {
    /**
     * this function appends html to body
     * @returns
     */
    jqueryObj : null,

    init: function () {
        this.jqueryObj = $("body .loader-container");

        if (this.jqueryObj.length > 0) {
            return this;
        }

        var html = '<div class="loader-container" style="display:none">';
            html += '<div class="loader-content">';
                html += '<div class="loader-icon spinner-border"></div>';        
                html += '<div class="loader-info" style="display:none"></div>';                
                html += '<div class="loader-footer" style="display:none"></div>';        
            html += "</div>";
        html += "</div>";

        $("body").append(html).addClass("loader-applied");
        
        this.jqueryObj = $("body .loader-container");

        return this;
    },
   
    setInfo : function(html) {
        if (html === false)
        {
            this.jqueryObj.find(".loader-info").html("").hide();
        }

        if (typeof(html) == "string")
        {
            this.jqueryObj.find(".loader-info").html(html).show();
        }

        return this;
    },

    setFooter : function(html) {
        
        if (html === false)
        {
            this.jqueryObj.find(".loader-footer").html("").hide();
        }

        if (typeof(html) == "string")
        {
            this.jqueryObj.find(".loader-footer").html(html).show();
        }

        return this;
    },

    appendHtmlInFooter : function(html)
    {
        this.jqueryObj.find(".loader-footer").append(html);

        return this;
    },

    addCssClass : function(cssclass)
    {
        this.jqueryObj.find(".loader-content").addClass(cssclass);

        return this;
    },

    show: function () {
        
        this.jqueryObj.show();

        return this;
    },

    hide : function()
    {
        this.setInfo(false);

        this.setFooter(false);

        this.jqueryObj.fadeOut();

        return this;
    }
};