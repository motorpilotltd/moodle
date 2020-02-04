/**
 * Tour management code.
 *
 * @module     tool_usertours/managetours
 * @class      managetours
 * @package    tool_usertours
 * @copyright  2016 Andrew Nicols <andrew@nicols.co.uk>
 */
define(
    ['jquery', 'core/ajax', 'core/str', 'core/notification', 'core/config'],
    function ($, ajax, str, notification, mdlconfig) {
        var manager = {
            confirm: function (e) {
                e.preventDefault();

                str.get_strings([
                    {
                        key: 'cachenow_title',
                        component: 'local_reportbuilder'
                    },
                    {
                        key: 'cachenow',
                        component: 'local_reportbuilder'
                    },
                    {
                        key: 'yes',
                        component: 'moodle'
                    },
                    {
                        key: 'no',
                        component: 'moodle'
                    }
                ]).done(function (s) {
                    notification.confirm(s[0], s[1], s[2], s[3], $.proxy(function () {
                        var id = $(e.currentTarget).data('id');
                        var url = mdlconfig.wwwroot + '/local/reportbuilder/ajax/cachenow.php?reportid=' + id;
                        $.post(url, function (data) {
                            notification.alert(s[0], data);
                        });
                    }));
                });
            },

            /**
             * Setup the tour management UI.
             *
             * @method          setup
             */
            init: function () {
                $('body').delegate('[data-action="cachenow"]', 'click', manager.confirm);
            }
        };

        return manager;
    });
