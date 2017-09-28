(function($) {
    $.fn.maxlen = function () {
        return this.each(function () {
            var $elem = $(this);
            var _maxlength = $elem.attr('maxlength');
            
            if (_maxlength == 0) {
                console.log('Elements must have a maxlength attribute');
                return true; // continue
            }
            
            // add a counter element
            $elem.wrap('<div class="counterwrap"></div>');
            $elem.parent().append('<span class="textcount"><span class="currentcount">'+$(this).val().length+'</span>/'+_maxlength+'</span>');
            $elem.parent().css({width: $elem.outerWidth()});
            // shim the input so there is padding for counter, but adjust width
            // accordingly so that it remains the same
            var _countwidth = 6 + $elem.parent().find('.textcount').outerWidth();
            $elem.css({'padding-right': 2 + _countwidth, width: 4 + $elem.width() - _countwidth});
            
            // check for changes, update
            $elem.on('input propertychange', function () {
                $elem.parent().removeClass('warning');
                $elem.parent().find('.currentcount').text($(this).val().length);
                
                if ($(this).val().length >= _maxlength) {
                    $elem.parent().addClass('warning');
                }
            });
        });
    };
}(jQuery));