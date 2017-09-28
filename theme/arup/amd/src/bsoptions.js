/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap', 'core/log'], function($, bootstrap, log) {

  "use strict"; // jshint ;_;

  log.debug('Arup Bootstrap AMD opt in functions');

    return {
        init: function() {
            $(document).ready(function($) {
                $('body').tooltip({selector: '[data-toggle=tooltip]'});
                $('body').popover({selector: '[data-toggle=popover]'});
                $('body').on('click', '[data-toggle=popover]', function(e){
                    e.preventDefault();
                });
            });
            log.debug('Arup Bootstrap AMD init');
        }
    }
});
/* jshint ignore:end */