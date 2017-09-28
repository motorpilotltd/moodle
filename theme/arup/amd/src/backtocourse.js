/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('backtocourse');

    return {
        init: function(sectionnum, injectsections) {
            log.debug('backtocourse | init | sectionnum ' + sectionnum + ' | injectsections' + injectsections);
            var sectionifycourselinks = function (sectionnum) {
                var booOnePage = $('body').hasClass('format-aruponepage');
                $('.navbar .breadcrumb, #page-content div[role="main"]')
                        .find("a[href*='course/view']")
                        .each(function (i, link) {
                            if (!link.href.match('section')) {
                                link.href += booOnePage === true ? '&section='+sectionnum : '#section-'+sectionnum;
                                log.debug(link.href);
                            }

                });
            };
            var sectionifyforms = function (sectionnum) {
                $("form[action*='course/view']").each(function () {
                    if ($('body').hasClass('format-aruponepage')) {
                        if ($(this).find("input[name='section']").length === 0) {
                            $(this).append('<input type="hidden" name="section" value="'+sectionnum+'" />');
                        }
                    } else {
                        var action = $(this).attr('action');
                        $(this).attr('action', action + '#section-' + sectionnum);
                    }
                });
            };

            $(document).ready(function () {
                if (injectsections && sectionnum) {
                    sectionifycourselinks(sectionnum);
                    sectionifyforms(sectionnum);
                }
            });
        }
    };
});
/* jshint ignore:end */