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
define(['jquery', 'core/modal_factory', 'core/templates', 'core/str', 'core/notification'],
    function ($, ModalFactory, Templates, Str, Notification) {

        var showhidecolumnsmodal = {
            init: function (id, shortname, columnscontext) {
                var addblocklink = $("div.showhidecolumns button");

                var titlePromise = Str.get_string('showhidecolumns', 'local_reportbuilder')
                    .fail(Notification.exception);

                var bodyPromise =Templates.render('local_reportbuilder/showhidecolumnsmodal', columnscontext)
                        .fail(Notification.exception);

                ModalFactory.create({
                    title: titlePromise,
                    body: bodyPromise,
                    type: ModalFactory.types.DEFAULT,
                }, addblocklink);

                $('body').on('click', '#column-checkboxes input', function () {
                    var selheader = '#' + shortname + ' th.' + $(this).attr('name');
                    var sel = '#' + shortname + ' td.' + $(this).attr('name');
                    var value = $(this).is(':checked') ? 1 : 0;

                    $(selheader).toggle();
                    $(sel).toggle();

                    $.ajax({
                        url: M.cfg.wwwroot + '/local/reportbuilder/showhide_save.php',
                        data: {
                            'shortname': shortname,
                            'column': $(this).attr('name'),
                            'value': value,
                            'sesskey': M.cfg.sesskey
                        }
                    });
                });
            }
        };

        return showhidecolumnsmodal;
    });