(function($) {
    $.fn.sadropdown = function(options) {
        var settings = $.extend({
            relativeTo : "body"
        }, options);

        var win = $(window);
        return this.each(function() {
            $(this).on('click', function() {
                
                var droptrgr = $(this);
                var dropdn = $(this).next(".dropdown-menu");
                var boundry = (settings.relativeTo!=="body") ? $(this).closest(settings.relativeTo) : $("body");
                
                var winHeight = win.height();
                var viewportOffset = droptrgr.offset().top - win.scrollTop();
                var dropdownHeight = dropdn.outerHeight() + droptrgr.outerHeight();
                var dropupArea = winHeight - dropdownHeight;
                
                var boundryht = boundry.outerHeight();
                var boundryOffset = droptrgr.offset().top - boundry.offset().top;                
                var dropArea = boundryht - dropdownHeight;

                if (viewportOffset > dropupArea || boundryOffset > dropArea) {
                         dropdn.css({top: "auto", bottom: "45%"});
                } else {
                    dropdn.css({bottom: "auto", top: "100%"});
                }
                
            });
        });
    };
}(jQuery));