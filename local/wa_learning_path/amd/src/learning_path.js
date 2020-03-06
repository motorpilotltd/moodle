/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap', 'core/str'], function($, bootstrap) {

    "use strict"; // jshint ;_;
    return {
        init: function() {
            var loadModal = function(url) {
                $.get(url, function(data) {
                    $('#myModal .modal-body').html(data);
                    var print_button = $('#myModal .modal-body #print_section').first();
                    $('#myModal #print_placeholder').replaceWith(print_button);
                    $('#myModal .modal-body').find('#print_section').hide();
                    $('#myModal').modal('show');

                    $('#myModal .matrix_activities .activity_completion').click(function(){
                        tickCompletion($(this));
                    });

                    $('#myModal #print_section').click(function(){
                        var levels = '';
                        var regions = '';

                        if($('.levels').val()) {
                            levels = $('.levels').val().join();
                        }

                        if($('.region').val()) {
                            regions = $('.region').val().join();
                        }

                        var url = $(this).prop('href') + '&levels=' + levels + '&regions=' + regions;
                        $(this).prop('href', url);
                    });

                    $('form.filtration :checkbox, form.filtration select').on('change', function() {
                        updateFiltered();
                    });

                    $('.navigate-cells').on('click', function() {
                        loadModal($(this).attr("data-url"));


                        // Now return a false (negating the link action) to prevent Bootstrap's JS 3.1.1
                        // from throwing a 'preventDefault' error due to us overriding the anchor usage.
                        return false;
                    });
                });


            };

            var tickCompletion = function(img) {

                var status = img.hasClass('no') ? 1 : 0;
                var activityid = img.attr('data-id');
                var learning_path_id = img.attr('data-lpathid');
                var completion_url = img.attr('data-url');

                // If completing and data-cpd is 1 need to confirm.
                if (status && img.data('cpd')) {
                    var result = window.confirm(str.get_string('confirm:cpdupload', 'local_wa_learning_path'));
                    if (!result) {
                        return false;
                    }
                }
                $.ajax({
                    method: "POST",
                    url: completion_url,
                    data: { activityid: activityid, learningpathid: learning_path_id,completion: status }
                })
                    .done(function( returnData ) {
                        var cell = img.parent();
                        $(cell).html( returnData );

                        $(cell).find('.activity_completion').on('click', function(){
                            tickCompletion($(this));
                        });
                    });

                return false;
            };

            var updateFiltered = function() {
                var positions = $('#myModal form.filtration .filter_position :checkbox:checked').map(function(){
                    return $(this).val();
                }).get();
                var percents = $('#myModal form.filtration .filter_percent :checkbox:checked').map(function(){
                    return parseInt($(this).val());
                }).get();
                var methodology = $('#myModal form.filtration .filter_methodology select').val();

                $('#myModal table.list_of_activities tr').each(function(){
                    var hide = false;

                    if (methodology !== '' && $(this).data('methodology') !== methodology) {
                        hide = true;
                    }

                    if ($.inArray($(this).data('position'), positions) === -1) {
                        hide = true;
                    }

                    if (percents.length < 3 && $.inArray(parseInt($(this).data('percent')), percents) === -1) {
                        hide = true;
                    }

                    if(hide) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            };

            $(document).ready(function($) {
                $('a[data-toggle="modal"]').on('click', function() {
                    loadModal(this.href);

                    // Now return a false (negating the link action) to prevent Bootstrap's JS 3.1.1
                    // from throwing a 'preventDefault' error due to us overriding the anchor usage.
                    return false;
                });

                $('.cell_icon_container').on('mouseover', function() {
                    var id = $(this).parent().attr('id');
                    var col_and_row = id.replace('#','').split('_');
                    var col = col_and_row[0];
                    var row = col_and_row[1];

                    $('.lp_cell').each(function() {
                        $(this).removeClass('cell-highlight');

                        var current_col_and_row = $(this).attr('id').replace('#','').split('_');
                        var current_col = current_col_and_row[0];
                        var current_row = current_col_and_row[1];

                        if(current_col == col || current_row == row) {
                            $(this).addClass('cell-highlight');
                        }
                    });
                });
            });
        },
    }
});
/* jshint ignore:end */