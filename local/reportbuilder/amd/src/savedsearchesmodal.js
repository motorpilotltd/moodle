/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@t0taralms.com>
 * @author Valerii Kuznetsov <valerii.kuznetsov@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Javascript file containing JQuery bindings for processing changes to instant filters
 */
define(['jquery', 'core/modal_factory', 'core/templates', 'core/str', 'core/notification', 'core/modal_events'],
    function ($, ModalFactory, Templates, Str, Notification, ModalEvents) {

        var managesavedsearchesmodal = {
            init: function (id) {
                var addblocklink = $("div.managesavedsearches button, div.managesavedsearches input");

                var titlePromise = Str.get_string('savedsearches', 'local_reportbuilder')
                    .fail(Notification.exception);

                var bodyPromise = $.ajax({
                    url: M.cfg.wwwroot + '/local/reportbuilder/savedsearches.php?id=' + id.toString()
                }).fail(Notification.exception);

                ModalFactory.create({
                    title: titlePromise,
                    body: bodyPromise,
                    type: ModalFactory.types.DEFAULT,
                }, addblocklink);

                $('body').on('click', 'a.delete-search', function (e) {
                    e.preventDefault();

                    var titlePromise = Str.get_string('confirm')
                        .fail(Notification.exception);

                    var sid = $(this).attr('data-searchid');

                    var bodyPromise = $.ajax({
                        url: M.cfg.wwwroot + '/local/reportbuilder/savedsearches.php?action=delete&id=' + id.toString() + '&sid=' + sid
                    }).fail(Notification.exception);

                    ModalFactory.create({
                        type: ModalFactory.types.CONFIRM,
                        title: titlePromise,
                        body: bodyPromise,
                    })
                        .done(function(modal) {
                            modal.getRoot().on(ModalEvents.yes, function() {
                                $.ajax({
                                    url: M.cfg.wwwroot + '/local/reportbuilder/savedsearches.php?sesskey=' + M.cfg.sesskey + '&confirm=true&action=delete&id=' + id.toString() + '&sid=' + sid
                                })
                                    .fail(Notification.exception)
                                    .then(function() {
                                        $('.savedsearchid_' + sid).remove();
                                    });
                            });
                            modal.show();

                            modal.getRoot().on(ModalEvents.hidden, function() {
                                modal.destroy();
                            });
                        });
                });

                $('body').on('click', 'a.edit-search', function (e) {
                    e.preventDefault();

                    var titlePromise = Str.get_string('savedsearches', 'local_reportbuilder')
                        .fail(Notification.exception);

                    var sid = $(this).attr('data-searchid');

                    var bodyPromise = $.ajax({
                        url: M.cfg.wwwroot + '/local/reportbuilder/savedsearches.php?action=edit&id=' + id.toString() + '&sid=' + sid
                    }).fail(Notification.exception);

                    ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        title: titlePromise,
                        body: bodyPromise,
                    })
                        .done(function(modal) {
                            modal.getRoot().on(ModalEvents.save, function() {
                                var name = $("#id_name").val();
                                var ispublic = $("#id_ispublic").val();

                                $.ajax({
                                    url: M.cfg.wwwroot + '/local/reportbuilder/savedsearches.php?sesskey=' + M.cfg.sesskey
                                        + '&confirm=true&action=edit&id=' + id.toString()
                                        + '&sid=' + sid
                                        + '&name=' + name
                                        + '&ispublic=' + ispublic
                                })
                                    .fail(Notification.exception)
                                    .then(function() {
                                        $('.savedsearchid_' + sid + ' .c0').text(name);

                                        if (ispublic == 1) {
                                            Str.get_string('yes').then(
                                                function (string) {
                                                    $('.savedsearchid_' + sid + ' .c1').text(string);
                                                }
                                            );
                                        } else {
                                            Str.get_string('no').then(
                                                function (string) {
                                                    $('.savedsearchid_' + sid + ' .c1').text(string);
                                                }
                                            );
                                        }
                                    });
                            });

                            modal.getRoot().on(ModalEvents.hidden, function() {
                                modal.destroy();
                            });
                            modal.show();
                        });
                });
            }
        };

        return managesavedsearchesmodal;
    });