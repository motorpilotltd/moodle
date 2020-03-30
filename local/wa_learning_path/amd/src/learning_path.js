/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap', 'core/str', 'local_wa_learning_path/select2'], function($, bootstrap, str) {

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
                        if ($(this).data('cpd') === 1 && $(this).hasClass('no')) {
                            $('#completionModal').modal('show', $(this));
                        } else {
                            tickCompletion($(this));
                        }
                    });

                    $('#myModal #print_section').click(function(){
                        var levels = '';
                        var regions = '';
                        var roles = '';

                        if($('select[name="levels"]').val()) {
                            levels = $('select[name="levels"]').val().join();
                        }

                        if($('select[name="roles"]').val()) {
                            roles = $('select[name="roles"]').val().join();
                        }

                        if($('.region').val()) {
                            regions = $('.region').val().join();
                        }

                        var url = $(this).prop('href') + '&levels=' + levels + '&regions=' + regions + '&role=' + roles;
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
                var activity_notes = $('#completionModal #notes').val();
                var date_completed = Date.parse($('#completionModal #date').val()) / 1000;

                $.ajax({
                    method: "POST",
                    url: completion_url,
                    data: { activityid: activityid, learningpathid: learning_path_id, completion: status,
                        datecompleted: date_completed, activitynotes: activity_notes }
                })
                    .done(function( returnData ) {
                        var cell = img.parent();
                        $(cell).html( returnData );

                        $(cell).find('.activity_completion').on('click', function(){
                            if ($(this).data('cpd') === 1 && $(this).hasClass('no')) {
                                $('#completionModal').modal('show', $(this));
                            } else {
                                tickCompletion($(this));
                            }
                        });
                    });

                return false;
            };

            var saveCompletionNotes = function(button) {

                var confirm = $('#completionModal #confirmation').is(':checked');
                var date_completed = Date.parse($('#completionModal #date').val()) / 1000;

                if (!date_completed || !confirm) {

                    if (!date_completed) {
                        $('#completionModal .label-date').css('color', 'red');
                    }

                    if (!confirm) {
                        $('#completionModal .label-confirmation').css('color', 'red');
                    }
                    return false;
                }

                tickCompletion($('.cpd_currently_completing'));

                return true;
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

            var clearCompletionModal = function () {
                var header = $("#myModal .matrix-activities-title");
                var rlabel = header.data("rlabel");
                var clabel = header.data("clabel");
                $("#completionModal").find("#completionModalLabel2").text(clabel);
                $("#completionModal").find("#completionModalLabel3").text(rlabel);

                $('#completionModal #confirmation').prop("checked", false);
                $('#completionModal #notes').val("");
                $('#completionModal #date').val(new Date().toISOString().substr(0, 10));

                $('#completionModal .label-confirmation').css('color', '#3c3c3b');
                $('#completionModal .label-date').css('color', '#3c3c3b');
            }

            var updateLevels = function(urlFromSelect) {
                var levels = '';
                var regions = '';
                var roles = '';

                if($('select[name="levels"]').val()) {
                    levels = $('select[name="levels"]').val();
                }

                if($('select[name="roles"]').val()) {
                    roles = $('select[name="roles"]').val();
                }

                if($('.learning_path_matrix .region').val()) {
                    regions = $('.learning_path_matrix .region').val();
                }

                var url = urlFromSelect + '&levels=' + levels + '&regions=' + regions + '&role=' + roles;
                window.location = url;
            };

            $(document).ready(function($) {
                $('#completionModal #cancel2').on('click', function () {
                    $('#completionModal').modal('hide');
                });

                $('#completionModal #save2').on('click', function () {

                    if (saveCompletionNotes($(this))) {
                        $('#completionModal').modal('hide');
                    }
                });

                $('select[name="levels"]').select2({
                    placeholder: "Select level",
                });

                $('select[name="levels"]').on('select2:select', function() {
                    updateLevels($(this).data('url'));
                });

                $('select[name="levels"]').on('select2:unselect', function() {
                    updateLevels($(this).data('url'));
                });

                $('a[data-toggle="modal"]').on('click', function() {
                    loadModal(this.href);

                    // Now return a false (negating the link action) to prevent Bootstrap's JS 3.1.1
                    // from throwing a 'preventDefault' error due to us overriding the anchor usage.
                    return false;
                });

                $('#completionModal').on('show.bs.modal', function (event) {
                    var myModal = $('#myModal .modal-content');
                    var img = $(event.relatedTarget);
                    img.addClass('cpd_currently_completing');
                    myModal.append($('<div></div>').addClass('modal-gray-out'));
                    clearCompletionModal();
                });

                $('#completionModal').on('hide.bs.modal', function (event) {
                    $('.activity_completion').removeClass('cpd_currently_completing');
                    $('.modal-gray-out').remove();
                });

                $("#myModal").on('hide.bs.modal', function(){
                    $('.popover').remove();
                });

                $('.cell_icon_container').on('mouseenter', function() {
                    var id = $(this).parent().attr('id');
                    var col_and_row = id.replace('#','').split('_');
                    var col = col_and_row[0];
                    var row = col_and_row[1];

                    $('.lp_cell').each(function() {
                        $(this).removeClass('cell-highlight');
                        $(this).removeClass('marked-cell');

                        var current_col_and_row = $(this).attr('id').replace('#','').split('_');
                        var current_col = current_col_and_row[0];
                        var current_row = current_col_and_row[1];

                        if(current_col == col || current_row == row) {
                            $(this).addClass('cell-highlight');
                        }
                    });

                    $('.lp_row_header').each(function() {
                        $(this).removeClass('cell-highlight');

                        if($(this).data('row') == row) {
                            $(this).addClass('cell-highlight');
                        }
                    });

                    $('.lp_col_header').each(function() {
                        $(this).removeClass('cell-highlight');

                        if($(this).data('column') == col) {
                            $(this).addClass('cell-highlight');
                        }
                    });

                    $(this).parent().removeClass('cell-highlight');
                    $(this).parent().addClass('marked-cell');
                });

                $('.cell_icon_container').on('mouseleave', function() {
                    $('.lp_cell').each(function() {
                        $(this).removeClass('cell-highlight');
                        $(this).removeClass('marked-cell');
                    });

                    $('.lp_row_header').each(function() {
                        $(this).removeClass('cell-highlight');
                    });

                    $('.lp_col_header').each(function() {
                        $(this).removeClass('cell-highlight');
                    });
                });
            });
        },
    }
});
/* jshint ignore:end */