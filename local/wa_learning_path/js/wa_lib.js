/*
 * Javascript library.
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */

var wa_learning_path = {
	/**
	 * Show or hide item content.
	 * @param {type} item
	 * @returns {Boolean}
	 */
	showHideItemContent: function (item){
		var id = $(item).attr('data-id');
		var plus_minus = $(item).parent().parent().find('.plus-minus');

		if($('#description_' + id).hasClass('short')) {
			$('#description_' + id).css({ height: "100%"});
			$('#description_' + id).removeClass('short').addClass('full');
			plus_minus.removeClass('glyphicon-plus').addClass('glyphicon-minus');
		} else {
			$('#description_' + id).css({ height: "20px"});
			$('#description_' + id).removeClass('full').addClass('short');
			plus_minus.removeClass('glyphicon-minus').addClass('glyphicon-plus');
		}
		return false;
	}
}
