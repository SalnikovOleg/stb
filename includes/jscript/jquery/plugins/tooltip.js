(function($) {
    $.fn.easyTooltip = function(options) {
        var defaults = {
            xOffset: 10,
            yOffset: 20,
            tooltipId: "easyTooltip",
            clickRemove: false,
            content: "",
            useElement: ""
        };
        var options = $.extend(defaults, options);
        var content;
        this.each(function() {
            var title = $(this).attr("title");
            $(this).hover(function(e) {
                content = (options.content != "") ? options.content: title;
                content = (options.useElement != "") ? $("#" + options.useElement).html() : content;
                $(this).attr("title", "");
                if (content != "" && content != undefined) {
                    $("body").append("<div id='" + options.tooltipId + "'>" + content + "</div>");
 $("#" + options.tooltipId).css("position", "absolute").css("top", (e.pageY - options.yOffset) + "px").css("left", (e.pageX + options.xOffset) + "px").css("display", "none").fadeIn("slow")

                }
            },
            function() {
                $("#" + options.tooltipId).remove();
                $(this).attr("title", title)
            });
			/*
            $(this).mousemove(function(e) {
if($(document).width() / 2 < e.pageX){
			$("#" + options.tooltipId)
                              
				.css("top",(e.pageY - options.yOffset-options.yOffset) + "px")
				.css('left',(e.pageX - $("#" + options.tooltipId).width() - options.xOffset- options.xOffset) + "px")
                             .css("display", "none").fadeIn("fast")
		} else {
			$("#" + options.tooltipId)
                           
				.css("top",(e.pageY - options.yOffset) + "px")
				.css("left",(e.pageX + options.xOffset) + "px")
                             .css("display", "none").fadeIn("fast")
		}
            });
			*/
            if (options.clickRemove) {
                $(this).mousedown(function(e) {
                    $("#" + options.tooltipId).remove();
                    $(this).attr("title", title)
                })
            }
        })
    }
})(jQuery);