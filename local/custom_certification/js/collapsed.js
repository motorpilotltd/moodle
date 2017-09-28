$('.coursesetbox.overview-coursesetbox>h3').click(function(){
	var box = $(this).parents('.overviewcourseset');
	if(box.hasClass('collapsed')) {
		box.removeClass('collapsed');
	} else {
		box.addClass('collapsed');
	}
});