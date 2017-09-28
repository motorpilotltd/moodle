/* jshint ignore:start */
define(['jquery', 'core/log', 'jqueryui'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('mod/threesixty spiderweb');

    $.fn.spiderweb = function() {
        var form = this;
        var option0 = form.find('select option[value="0"]');

        form.children('select').change(function(){
            show($(this).find(':selected').val());
        });

        var newimages=[];

        $('img.threesixty-spiderweb').each(function(){
            var that = $(this);
            var i = that.data('id');
            newimages[i] = {};
            newimages[i].that = that;
            newimages[i].image = new Image();
            newimages[i].image.id = i;
            newimages[i].image.onload = function(){
                var i = this.id;
                var that = newimages[i].that;
                var $this = $(this);
                $this.removeAttr('id');
                $this.width(640);
                $this.height(640 * 1.05);
                $this.attr('class', that.attr('class'));
                that.replaceWith($this);
                option0.data('count', option0.data('count')+1);
                option0.text(option0.data('text').replace('[[x]]', option0.data('count')));
                if (option0.data('count') === option0.data('total')) {
                    option0.remove();
                }
                var option = form.find('select option[value="'+i+'"]');
                option.text(option.data('title')).prop('disabled', false);
                if ($this.is(':visible')) {
                    var wrapper = $this.parents('.threesixty-spiderweb-wrapper');
                    $(wrapper).height($(wrapper).width() * 1.06);
                    $this.resizable({containment: wrapper, aspectRatio: true});
                }
            };
            newimages[i].image.src = newimages[i].that.data('src');
        });

        return form;
    };

    function show(i) {
        var which = '#threesixty-spiderweb-wrapper-' + i;
        var $siblings = $('.threesixty-spiderweb-wrapper:not('+which+')').fadeOut('slow');
        $siblings.promise().done(function(){
            var $wrapper = $(which);
            $wrapper.fadeIn('slow').height($wrapper.width() * 1.06);
            var image = $wrapper.find('img');
            if (typeof image.data('src') === 'undefined') {
                image.resizable({containment: which, aspectRatio: true});
            }
        });
    };

    return {
        init: function(target) {
            $('#threesixty-spiderweb-select').spiderweb();
            log.debug('mod/threesixty spiderweb init');
        }
    };

});
/* jshint ignore:end */