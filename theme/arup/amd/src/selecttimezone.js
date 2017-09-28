/* jshint ignore:start */
define(['jquery', 'core/config', 'theme_bootstrap/bootstrap', 'core/log'], function($, cfg, bootstrap, log) {

  "use strict"; // jshint ;_;

  log.debug('Arup Timezone Selector');

    return {
        init: function() {
            $(document).ready(function($) {
                $('.setusertime').click(function(event) {
                    event.preventDefault();
                    $('#updatetimezone').modal('show');
                });

                $('#timezoneselector').change( function() {
                    var selected = $(this).find(":selected").text();

                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/theme/arup/ajax.php?sesskey=' + cfg.sesskey,
                        data: {action: 'settimezone', value: selected },
                        success: function(data){
                            // Failure if object not returned.
                            
                            if (typeof data !== 'object') {
                                data = {
                                    success: false,
                                    message: s,
                                    data: ''
                                };
                            }
                            if (data.success) {
                                log.debug('success');
                                $('.setusertime').html(data.data);
                                $('#updatetimezonesuccess').removeClass('hidden');
                            }
                            
                        },
                        error: function(){
                            log.debug('fail');
                        },
                        complete: function() {
                            log.debug('complete');
                        }
                    });

                });
                
            });
            log.debug('Arup Select Timezone done');
        }
    }
});
/* jshint ignore:end */