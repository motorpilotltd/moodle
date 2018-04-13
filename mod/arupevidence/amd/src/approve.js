// This file is part of Moodle - http://moodle.org/
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
 * View page JS.
 *
 * @package    modal_arupevidence
 * @copyright  2018 Xantico Ltd
 * @author     Aleks Daloso
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery', 'core/config', 'core/str', 'core/notification', 'theme_bootstrap/bootstrap'],
    function($, cfg, str, notification) {

        return /** @alias module:mod_arupevidence/approve */ {
            init: function () {
                var ae_userid = '',
                    cm_id = '',
                    actiontype = '',
                    reject_modal = $('#reject-evidence-modal'),
                    approve_modal = $('#approve-evidence-modal'),
                    modal = $('.modal');
                $('.action-evidence').on('click', function(e) {
                    e.preventDefault();
                    ae_userid = $(this).data('ahbuserid');
                    cm_id = $(this).data('cmid');
                    actiontype = $(this).data('actiontype');

                    $('#'+ actiontype +'-evidence-modal').modal('show');
                });


                modal.on('shown.bs.modal', function () {
                    // Add event listener to modal submit buttton
                    var _thismodal = $(this);
                    _thismodal.on('click', '#'+ actiontype +'-evidence-btn', function () {
                        var _thisbtn = $(this);
                        _thisbtn.prop('disabled', true);
                        str.get_string('label:processing', 'mod_arupevidence').done(function(s) {
                            _thisbtn.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                        }).fail(notification.exception);
                        var rejectmessage = _thismodal.find('#reject-message').length != 0 ? _thismodal.find('#reject-message').val() : '' ;
                        $.ajax({
                            async: false,
                            type: 'POST',
                            url: cfg.wwwroot + '/mod/arupevidence/ajax.php',
                            data: {
                                action: actiontype,
                                ae_userid: ae_userid,
                                id: cm_id,
                                reject_message: rejectmessage
                            },
                            success: function(res) {
                                location.reload();
                            },
                            error: function(e){
                                location.reload();
                            }
                        });
                        _thismodal.modal('hide');
                    });
                });
            }
        }
    }
);