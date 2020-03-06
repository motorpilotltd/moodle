/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap'], function($, bootstrap) {

    "use strict"; // jshint ;_;
    return {
        init: function() {
            var moveColumn = function(columnId, position) {
                var index = getColumnPosition(columnId);
                var amountOfColumns = $('.wa_matrix .col_header').length;
                var swapUntil = parseInt(position)+1;
                var column;

                // moving column from left to right
                if(index <swapUntil) {
                    for(column=2; column < amountOfColumns+1; column++) {
                        if(column >= index && column < swapUntil) {
                            $('.wa_matrix > div').each(function (j) {
                                $(this).find('> div:nth-child('+parseInt(column)+')').swapWith($(this).find('> div:nth-child('+(parseInt(column)+1)+')'));
                            });
                        }
                    }
                }

                // movign column from right to left
                if(index > swapUntil) {
                    for(column=parseInt(amountOfColumns)+1; column > 1; column--) {
                        if(column <= index && column > swapUntil) {
                            $('.wa_matrix > div').each(function (j) {
                                $(this).find('> div:nth-child('+parseInt(column)+')').swapWith($(this).find('> div:nth-child('+(parseInt(column)-1)+')'));
                            });
                        }
                    }
                }

            }

            var moveRow = function(rowId, position) {
                var index = getRowPosition(rowId);
                var amountOfRows = $('.wa_matrix .wa_row').length;
                var swapUntil = parseInt(position)+1;
                var row;
                // moving row from top to bottom
                if(index <swapUntil) {
                    for(row=2; row < amountOfRows+1; row++) {
                        if(row >= index && row < swapUntil) {
                            $('.wa_matrix').each(function (j) {
                                $(this).find('> div:nth-child('+parseInt(row)+')').swapWith($(this).find('> div:nth-child('+(parseInt(row)+1)+')'));
                            });
                        }
                    }
                }

                // movign column from bottom to top
                if(index > swapUntil) {
                    for(row=parseInt(amountOfRows)+1; row > 1; row--) {
                        if(row <= index && row > swapUntil) {
                            $('.wa_matrix').each(function (j) {
                                $(this).find('> div:nth-child('+parseInt(row)+')').swapWith($(this).find('> div:nth-child('+(parseInt(row)-1)+')'));
                            });
                        }
                    }
                }

            }


            var calculate_width_and_height = function () {
                var height = 0;
                var row_amount = $('.wa_row').length;
                $('.wa_row .row_header').each(function() {
                    height = height + $(this).height();
                });

                $('.wa_row .row_header').each(function() {
                    // $(this).height( height / amount );
                    $(this).height( height / row_amount);
                    $(this).width(120);
                })

                var width = $('.cols').width() - 120;
                var col_amount = $('.col_header').length -1;

                $('.col_header').each(function() {
                    $(this).width( width / col_amount );
                });

                $('.cell').each(function() {
                    $(this).attr('style', 'width: '+ (width / col_amount) + 'px !important;height: '+ (height / row_amount) + 'px !important;');

                    // $(this).height( (height / amount));
                });

                $('.add_row').width( $('.wa_matrix').width() - 20 );
            }

            var getColumnPosition = function(columnId) {
                var col_header = $('#'+columnId);
                var i = $('.wa_matrix .cols .col_header').index(col_header);

                return parseInt(i) + 2;
            };

            var getRowPosition = function(columnId) {
                var col_header = $('#'+columnId);
                var i = $('.wa_matrix .wa_row').index(col_header);

                return parseInt(i) + 2;
            };

            var updateVisibility = function(objectId, visible, type) {
                if(type == 'column') {
                    if(visible == true) {
                        $('#' + objectId).attr('show', 'yes');
                    } else {
                        $('#' + objectId).attr('show', 'no');
                    }
                }

                if(type=='row') {
                    if(visible == true) {
                        $('#' + objectId + ' .row_header').attr('show', 'yes');
                        if($('#' + objectId).hasClass('greyed')) {
                            $('#' + objectId).removeClass('greyed');
                        }
                    } else {
                        $('#' + objectId + ' .row_header').attr('show', 'no');
                        if(!$('#' + objectId).hasClass('greyed')) {
                            $('#' + objectId).addClass('greyed');
                        }
                    }
                }

                wa_set_greyed_cells();
            };

            var wa_set_greyed_cells = function() {
                $('.wa_matrix .cell').removeClass('greyed');

                $('.wa_matrix .col_header[show="no"]').each(function () {
                    var i = $('.wa_matrix .cols .col_header').index(this);
                    var index = parseInt(i) + 2;
                    $('.wa_matrix > div > div:nth-child(' + index + ')').addClass('greyed');
                });
            };

            $.fn.swapWith = function(to) {
                return this.each(function() {
                    var copy_to = $(to).clone(true);
                    var copy_from = $(this).clone(true);
                    $(to).replaceWith(copy_from);
                    $(this).replaceWith(copy_to);
                });
            };

            $(document).ready(function($) {
                $('.col_header a[data-toggle="modal"]').on('click', function() {
                    var columnId = $(this).parent().parent().parent().attr('id');
                    var name = $('#'+columnId+' .name .tooltip').text();
                    $('#myModalColumn #delete').attr('data-id', columnId);
                    $('#myModalColumn #save').attr('data-id', columnId);
                    $('#myModalColumn #name').val(name);
                    $('#myModalColumn #position').val(getColumnPosition(columnId)-1);

                    if($('#' + columnId).attr('show') == 'yes') {
                        $('#myModalColumn #visible').prop('checked', true);
                    } else {
                        $('#myModalColumn #visible').prop('checked', false);
                    }


                    $('#myModalColumn').modal('show');

                    // Now return a false (negating the link action) to prevent Bootstrap's JS 3.1.1
                    // from throwing a 'preventDefault' error due to us overriding the anchor usage.
                    return false;
                });

                $('#myModalColumn #delete').on('click', function() {
                    var columnId = $(this).attr('data-id');
                    var col_header = $('#'+columnId);
                    var i = $('.wa_matrix .cols .col_header').index(col_header);
                    var index = parseInt(i) + 2;
                    $('.wa_matrix > div > div:nth-child(' + index + ')').remove();
                    $('#myModalColumn').modal('hide');

                    calculate_width_and_height();
                });

                $('#myModalColumn #cancel').on('click', function() {
                    $('#myModalColumn').modal('hide');
                });

                $('#myModalColumn #save').on('click', function() {
                    var name = $('#myModalColumn #name').val();
                    var shortenName = name.replace(/^(.{18}[.]*).*/, "$1");
                    if(shortenName.length < name.length) {
                        shortenName = shortenName+'...';
                    }

                    var position = $('#myModalColumn #position').val();
                    var columnId = $(this).attr('data-id');
                    var visible = $('#myModalColumn #visible').is(':checked');
                    $('#'+columnId+' .name .tooltip').text(name);
                    $('#'+columnId+' .name .text').text(shortenName);

                    moveColumn(columnId, position);
                    updateVisibility(columnId, visible, 'column');
                    $('#myModalColumn').modal('hide');
                });

                $('.row_header a[data-toggle="modal"]').on('click', function() {
                    var rowId = $(this).parent().parent().parent().attr('id');
                    var name = $('#'+rowId+' .name .tooltip').text();
                    $('#myModalRow #delete').attr('data-id', rowId);
                    $('#myModalRow #save').attr('data-id', rowId);
                    $('#myModalRow #name').val(name);

                    $('#myModalRow #position').val(getRowPosition(rowId)-1);

                    if($('#' + rowId + ' .row_header').attr('show') == 'yes') {
                        $('#myModalRow #visible').prop('checked', true);
                    } else {
                        $('#myModalRow #visible').prop('checked', false);
                    }


                    $('#myModalRow').modal('show');

                    // Now return a false (negating the link action) to prevent Bootstrap's JS 3.1.1
                    // from throwing a 'preventDefault' error due to us overriding the anchor usage.
                    return false;
                });

                $('#myModalRow #delete').on('click', function() {
                    var rowId = $(this).attr('data-id');
                    $('#' + rowId).remove();

                    $('#myModalRow').modal('hide');
                });

                $('#myModalRow #cancel').on('click', function() {
                    $('#myModalRow').modal('hide');
                });

                $('#myModalRow #save').on('click', function() {
                    var name = $('#myModalRow #name').val();
                    var shortenName = name.replace(/^(.{20}[.]*).*/, "$1");
                    if(shortenName.length < name.length) {
                        shortenName = shortenName+'...';
                    }

                    var position = $('#myModalRow #position').val();
                    var rowId = $(this).attr('data-id');
                    var visible = $('#myModalRow #visible').is(':checked');
                    $('#'+rowId+' .name .tooltip').text(name);
                    $('#'+rowId+' .name .text').text(shortenName);

                    moveRow(rowId, position);
                    updateVisibility(rowId, visible, 'row');
                    $('#myModalRow').modal('hide');
                });

                $('.cell').on('mouseenter', function() {
                    var column = $(this).data('column');

                    $('.cell').each(function() {
                        $(this).removeClass('cell-highlight');

                        if($(this).data('column') == column) {
                            $(this).addClass('cell-highlight');
                        }
                    });

                    $(this).parent().find('.cell').addClass('cell-highlight');
                });
            });
        },
    }
});
/* jshint ignore:end */