/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap', 'core/log'], function($, bootstrap, log) {

    "use strict"; // jshint ;_;

    log.debug('Arup Boostrap AMD carousel');

    return {
        init: function() {
            $(document).ready(function($) {
                $('.carousel').carousel({
                    interval: 5000
                });
            });
            log.debug('Arup Boostrap AMD carousel init');
        }
    }
});
/* jshint ignore:end */