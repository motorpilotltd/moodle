/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   theme_imagehandler
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
[
    'jquery',
    'theme_arupboost/repository',
    'core/custom_interaction_events',
    'theme_arupboost/imagehandler_cropworker'
],
function(
    $,
    Repository,
    CustomEvents,
    Cropworker
) {

    var addCoverImageAlert = function(target, id, msg) {
        var closestr = M.util.get_string('closebuttontitle', 'moodle');
        if (!$(id).length) {
            target.find('#uploadbutton').before(
                '<div id="' + id + '" class="alert alert-warning state-element state1" role="alert">' +
                msg +
                '<button type="button" class="close" data-dismiss="alert" aria-label="' + closestr + '">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>'
            );
        }
    };

    /**
     * Get human file size from bytes.
     * http://stackoverflow.com/questions/10420352/converting-file-size-in-bytes-to-human-readable.
     * @param {int} size
     * @returns {string}
     */
    var humanFileSize = function(size) {
        var i = Math.floor(Math.log(size) / Math.log(1024));
        return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
    };

    var cleanState = function(target) {
        target.removeClass('state0');
        target.removeClass('state1');
        target.removeClass('state2');
        target.removeClass('state3');
        target.removeClass('state4');
        target.find('[role="alert"]').remove();
    };

    /**
     * First state - image selection button visible.
     */
    var state1 = function(target) {
        cleanState(target);
        target.addClass('state1');
        // TODO find a more elegant way to fix this.
    };

    /**
     * Second state - confirm / cancel buttons visible.
     */
    var state2 = function(target) {
        cleanState(target);
        target.addClass('state2');
    };

    /**
     * Third state - crop actions visible.
     */
    var state3 = function(target) {
        cleanState(target);
        target.addClass('state3');
    };

    /**
     *
     * @param {int} siteMaxBytes
     * @param {object} ajaxParams
     */
    var coverImage = function(target, siteMaxBytes, ajaxParams) {
        // Take a backup of what the current background image url is (if any).
        target.data('servercoverfile', target.css('background-image'));

        var file,
            filedata;

        CustomEvents.define(target, [CustomEvents.events.activate]);
        target.on(
            CustomEvents.events.activate,
            '[data-action="cropimage"]',
            function(e) {

                // Hide elements in the container when cropping.
                state3(target);

                // For image cropping we need a real <img> in the container.
                var originalImage = target.attr('data-original');

                var width = (target.width() / 100) * (90);
                var height = (target.height() / 100) * (90);

                // Cropy wants the native DOM element so using target[0].
                var croppedImage = new Cropworker(target[0], {
                    enableExif: true,
                    viewport: {
                        width: width,
                        height: height,
                        type: 'square'
                    }
                });
                croppedImage.bind({
                    url: originalImage,
                });

                var resultBtn = target.find('[data-action="crop-confirm"]');
                resultBtn.on('click', function() {
                    croppedImage.result('base64').then(function(imageData) {

                        ajaxParams.imagedata = imageData.split('base64,')[1];
                        ajaxParams.imagefilename = originalImage;
                        ajaxParams.cropped = 1;

                        Repository.saveImage({params: ajaxParams}).then(function(result) {
                            if (result.success) {
                                state1(target);
                                target.css('background-image', 'url(' + imageData + ')');
                                croppedImage.destroy();
                                location.reload();
                            }
                        });

                    });
                });
                var cancelBtn = target.find('[data-action="crop-cancel"]');
                cancelBtn.on('click', function() {
                    croppedImage.destroy();
                    state1(target);
                });
                e.preventDefault();
            }
        );

        target.find('.input-coverfiles').on('change',
            function(e) {
                var files = e.target.files; // FileList object.
                if (!files.length) {
                    return;
                }

                file = files[0];

                // Only process image files.
                if (!file.type.match('image.*')) {
                    return;
                }

                var reader = new FileReader();

                target.find('label[for="imagehandler-coverfiles"]').addClass('ajaxing');

                // Closure to capture the file information.
                reader.onload = (function(theFile) {
                    return function(e) {

                        // Set page header to use local version for now.
                        filedata = e.target.result;

                        // Warn if image file size exceeds max upload size.
                        // Note: The site max bytes is intentional, as the person who can do the upload would be able to
                        // override the course upload limit anyway.
                        var maxbytes = siteMaxBytes;

                        if (theFile.size > maxbytes) {
                            // Go back to initial state and show warning about image file size.
                            state1(target);
                            var maxbytesstr = humanFileSize(maxbytes);
                            var message = M.util.get_string('error:coverimageexceedsmaxbytes', 'theme_arupboost', maxbytesstr);
                            addCoverImageAlert(target, 'imagehandler-alert-cover-image-bytes', message);
                            return;
                        } else {
                            target.find('#imagehandler-alert-cover-image-bytes').remove();
                        }

                        // Warn if image resolution is too small.
                        var img = $('<img />');
                        img = img.get(0);
                        img.src = filedata;
                        $(img).on('load', function() {
                            if (img.width < 400) {
                                addCoverImageAlert(target, 'imagehandler-alert-cover-image-size',
                                    M.util.get_string('error:coverimageresolutionlow', 'theme_arupboost')
                                );
                            } else {
                                target.find('#imagehandler-alert-cover-image-size').remove();
                            }
                        });

                        target.css('background-image', 'url(' + filedata + ')');
                        target.data('localcoverfile', theFile.name);

                        state2(target);
                    };
                })(file);

                // Read in the image file as a data URL.
                reader.readAsDataURL(file);
            }
        );

        target.find('#imagehandler-changecoverimageconfirmation .ok').click(function(e) {

            if ($(this).parent().hasClass('disabled')) {
                return;
            }

            e.preventDefault();

            target.find('#imagehandler-alert-cover-image-size').remove();
            target.find('#imagehandler-alert-cover-image-bytes').remove();

            target.find('#imagehandler-changecoverimageconfirmation .ok').addClass('ajaxing');
            target.find('#imagehandler-changecoverimageconfirmation').addClass('disabled');

            var imageData = filedata.split('base64,')[1];

            ajaxParams.imagedata = imageData;
            ajaxParams.imagefilename = file.name;
            ajaxParams.cropped = 0;

            Repository.saveImage({params: ajaxParams}).then(function(result) {
                if (result.success) {
                    state1(target);
                    target.attr('data-original', result.fileurl);
                    location.reload();
                }
            });

        });
        target.find('#imagehandler-changecoverimageconfirmation .cancel').click(function() {
            if ($(this).parent().hasClass('disabled')) {
                return;
            }

            target.css('background-image', target.data('servercoverfile'));
            state1(target);
        });
        target.find('.uploadimagecontrols').addClass('imagehandler-js-enabled');
    };

    var updateImage = function(target, siteMaxBytes) {
        var ajaxParams = {
            imagefilename: null,
            imagedata: null,
            imageid: target.attr('data-imageid'),
            type: target.attr('data-type')
        };
        coverImage(target, siteMaxBytes, ajaxParams);
    };

    return {
        updateImage: updateImage
    };
});
