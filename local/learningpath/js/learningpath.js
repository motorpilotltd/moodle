$(document).ready(function(){
    
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

    // Close filters
    $('#arup_filter_block .filter_title').click();

    // Open location filter if viewing different location
    locationForm = $('#locationfilters').parents('form');
    if (locationForm.find('input[name=user-region]').val() != locationForm.find('input[name=region]').val()
        || locationForm.find('input[name=user-subregion]').val() != locationForm.find('input[name=subregion]').val()) {
        $('#locationfilters .filter_title').click();
    }
    
    $('select#id_filter_region').change(function(){
        var regionid = $('select#id_filter_region option').filter(':selected').val();
        var subregionid = $(this).parents('form').find('input[name=subregion]').val();
        $(this).parents('form').find('input[name=region]').val(regionid);
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot+"/local/learningpath/ajax-subregion.php",
            data: "regionid=" + regionid,
            success: function(html) {
                $('select#id_filter_subregion').html(html);
                $('select#id_filter_subregion option[value="'+subregionid+'"]').attr('selected',true);
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