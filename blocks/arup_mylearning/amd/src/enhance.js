//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Utility JS adding enhancements for block_arup_mylearning.
 *
 * @package    block_arup_mylearning
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config', 'block_arup_mylearning/purl'],
       function($, config, purl) {
    return /** @alias module:block_arup_mylearning/enhance */ {
        // Public variables and functions.
        /**
         * Add form enhancement via select2.
         *
         * @method initialise
         */
        initialise: function() {
            $('#block_arup_mylearning_content').children().each(function(){
                var tab = $(this).attr('id').match(/block_arup_mylearning_content_(.*)/);
                if ($(this).hasClass('hidden')) {
                    $.ajax({
                        url: config.wwwroot + '/blocks/arup_mylearning/ajax.php',
                        type: 'POST',
                        data: {
                            sesskey: config.sesskey,
                            action: 'content',
                            currenttab: tab[1],
                            instance: $('#block_arup_mylearning_content').data('instance')
                        },
                        success: function(data) {
                            if (data !== 'ERROR') {
                                $('#block_arup_mylearning_content_' + tab[1]).html(data);
                            }
                        }
                    });
                }
            });

            $(document).on('click', '.block_arup_mylearning_tabs a:not(.nolink)', function(){
                var url = purl($(this).attr('href'));
                var tab = '#block_arup_mylearning_tabs_' + url.param('tab');
                var content = '#block_arup_mylearning_content_' + url.param('tab');
                $(tab).css('visibility', 'visible').show().removeClass('hidden').siblings().hide();
                $(content).css('visibility', 'visible').show().removeClass('hidden').siblings().hide();
                return false;
            });

            var modalloader;
            $(document).on('click', 'a[data-toggle=modal]', function() {
                /* Extra functionality, normal event will still trigger and load data, etc. */
                var datatarget = $(this).data('target');
                var datalabel = $(this).data('label');
                modalloader = $(datatarget + ' .modal-body').html();
                $(datatarget + '-label').text(datalabel);
                $(datatarget + ' .modal-body').load($(this).data('url'));
            });

            $(document).on('hidden.bs.modal', '.modal', function () {
                /* Revert on close */
                $(this).data('modal', null);
                $('#info-modal-label').empty();
                $(this).find('.modal-body').html(modalloader);
            });

            $("textarea[maxlength]").bind('input propertychange', function() {
                var that = $(this);
                var maxLength = that.attr('maxlength');
                if (that.val().length > maxLength) {
                    that.val($(this).val().substring(0, maxLength));
                }
            });
        }
    };
});