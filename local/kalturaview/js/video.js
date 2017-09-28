require(['jquery'], function ($) {
    $('.video-holder').on('click', function () {
        var video = $(this).find('video');
        if (video.prop('paused')) {
            video.trigger('play');
        } else {
            video.trigger('pause');
        }
    });
    $('.video-holder video').on('play', function () {
        $(this).siblings('.overlay').hide();
    });
    $('.video-holder video').on('pause ended', function () {
        $(this).siblings('.overlay').show();
    });
});