define(['jquery'], function ($) {

    var el; // video holder in focus

    var interval;

    var delay = 500;
    var currentFrame = 0;
    var totalFrames = 10;
    var frameWidth = 230;

    var playSlides = function () {
        currentFrame++;
        if (currentFrame >= totalFrames) { // greater or equal as current frames is 0 indexed
            currentFrame = 0;
        }
        changeSlide();
    };

    var changeSlide = function () {
        if (el) {
            el.css({backgroundPosition: (-currentFrame * frameWidth) + 'px 0'});
        }
    };

    $('#tab-content-kaltura').on('mouseenter', '.image-holder', function () {
        el = $(this);
        interval = setInterval(playSlides, delay);
    });
    $('#tab-content-kaltura').on('mouseleave', '.image-holder', function () {
        // reset to 0
        currentFrame = 0;
        changeSlide();
        clearInterval(interval);
        el = null;
    });

    return {
        play: playSlides,
        change: changeSlide,
        apply: function () {
            return true;
        }
    };
});