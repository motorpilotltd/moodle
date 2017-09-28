$(document).ready(function(){
    if ('ontouchstart' in document.documentElement) {
        $('ul.arup_category_master').addClass('touch');
    }

    var alltoggles = $('ul.arup_category_master').find('span.accordion-toggle');
    alltoggles.removeClass('accordion-open').addClass('accordion-closed');
    alltoggles.find('span.accordion-plusminus').text('+');

    $('ul.arup_category_master').children('ul.arup_category').find('ul').hide();

    $('ul.arup_category li span.accordion-toggle a.accordion-link').click(function(){
        var that = $(this);
        var toggle = that.closest('span.accordion-toggle');
        var opensiblings = that.closest('ul').siblings('ul').find('span.accordion-open');
        var children = that.closest('li').children('ul');
        var numchildren = children.length;

        toggle.toggleClass('accordion-open').toggleClass('accordion-closed');
        if (toggle.hasClass('accordion-open')) {
            // Opening.
            that.children('span.accordion-plusminus').html('&ndash;');
        } else {
            // Closing.
            that.children('span.accordion-plusminus').text('+');
        }

        opensiblings.siblings('ul').slideUp(400, function() {
            opensiblings.removeClass('accordion-open').addClass('accordion-closed');
            opensiblings.find('span.accordion-plusminus').text('+');
        });

        if (toggle.hasClass('accordion-open')) {
            // Opening.
            children.slideDown();
        } else {
            // Closing.
            children.slideUp(400, function() {
                if (--numchildren > 0) {
                    return;
                }
                children.each(function() {
                    $(this).find('span.accordion-toggle').removeClass('accordion-open').addClass('accordion-closed');
                    $(this).find('ul').hide();
                    $(this).find('span.accordion-plusminus').text('+');
                });
            });
        }

        return false;
    });

    $('#arup_filter_block .filter_title').click(function(e){
        if (this == e.target) {
            that = $(this);
            if (that.hasClass('closed')) {
                that.siblings('.filter_filters').slideDown();
                that.removeClass('closed');
            } else {
                that.siblings('.filter_filters').slideUp();
                that.addClass('closed');
            }
        }
    });

    // Close filters.
    $('#arup_filter_block .filter_title').click();

    // Open location filter if viewing different location.
    locationForm = $('#locationfilters').parents('form');
    if (locationForm.find('input[name=user-region]').val() != locationForm.find('input[name=region]').val()
        || locationForm.find('input[name=user-subregion]').val() != locationForm.find('input[name=subregion]').val()) {
        $('#locationfilters .filter_title').click();
    }

    // Open advanced filter if an option is selected.
    $('#advancedfilters select option').filter(':selected').each(function(){
        if ($(this).val() != '') {
            $('#advancedfilters .filter_title').click();
            // Stop loop after first one found.
            return false;
        }
    });

    $('select#id_filter_region').change(function(){
        var regionid = $('select#id_filter_region option').filter(':selected').val();
        var subregionid = $(this).parents('form').find('input[name=subregion]').val();
        $(this).parents('form').find('input[name=region]').val(regionid);
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + "/local/accordion/ajax-subregion.php",
            data: "regionid=" + regionid,
            success: function(html) {
                $('select#id_filter_subregion').html(html);
                $('select#id_filter_subregion option[value="' + subregionid + '"]').attr('selected',true);
                if (regionid < 1 || $('select#id_filter_subregion option').length < 2) {
                    $('div#fitem_id_filter_subregion').hide();
                } else {
                    $('div#fitem_id_filter_subregion').show();
                }
            }
        });
    });

    $('select#id_filter_subregion').change(function(){
        var subregionid = $('select#id_filter_subregion option').filter(':selected').val();
        $(this).parents('form').find('input[name=subregion]').val(subregionid);
    });

    $('select#id_filter_subregion').change();
    $('select#id_filter_region').change();

    $('#showukmea').find('#fitem_id_submitbutton').hide();
    $('input#id_showukmea').change(function(){
        $(this).parents().filter('form').submit();
    });
});