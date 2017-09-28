(function ( $ ) {
console.log('lal tab');
$('.tabs li').on('click', 'a',function () {
   var $base = $(this).parents('div.description'); // get the TD containing tab set
   var _tab = $(this).attr('href').replace(/^[#]/, '.tab-');

   $base.find('.tabs li').removeClass('selected');
   $(this).parent('li').addClass('selected');
   $base.find('.tab').fadeOut(50).promise().done(function () {
       $base.find(_tab).addClass('tab-active').fadeIn(200);
   });
});

$('.tabs').find('li:first').addClass('selected');

}( jQuery ));