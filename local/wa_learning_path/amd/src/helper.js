/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap'], function($, bootstrap) {

    "use strict"; // jshint ;_;
    return {
        getColumnPosition: function(columnId) {
            var col_header = $('#'+columnId);
            var i = $('.wa_matrix .cols .col_header').index(col_header);

            return parseInt(i) + 2;
        },

        calculateWidthAndHeight: function () {
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

            var width = $('#main').width() - 55;
            var col_amount = $('.col_header').length -1;

            $('.col_header').each(function() {
                $(this).width( width / col_amount );
            });

            $('.cell').each(function() {
                $(this).attr('style', 'width: '+ (width / col_amount) + 'px !important;height: '+ (height / row_amount) + 'px !important;');
            });

            $('.add_row').width( $('.wa_matrix').width() - 20 );
        },

        moveColumn: function(columnId, position) {
            $.fn.swapWith = function(to) {
                return this.each(function() {
                    var copy_to = $(to).clone(true);
                    var copy_from = $(this).clone(true);
                    $(to).replaceWith(copy_from);
                    $(this).replaceWith(copy_to);
                });
            };

            var index = this.getColumnPosition(columnId);
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

        },

        updateRegionVisibility: function(columnId, regions) {
            $('.cols #' + columnId + ' .m3 span').each(function() {
                if(!$(this).hasClass('hide')) {
                    $(this).addClass('hide');
                }
            });

            regions.forEach(function(region) {
                $('.cols #' + columnId + ' .m3 span[id="'+region+'"]').removeClass('hide');
            });
        },

        updateVisibility: function(objectId, visible, type) {
            if(type == 'column') {
                if(visible == true) {
                    $('#' + objectId).attr('show', 'yes');
                    $('#' + objectId).removeClass('greyed');
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

            this.setHiddenCells();
        },

        setHiddenCells: function() {
            $('.wa_matrix .cell').removeClass('greyed');

            $('.wa_matrix .col_header[show="no"]').each(function () {
                var i = $('.wa_matrix .cols .col_header').index(this);
                var index = parseInt(i) + 2;
                $('.wa_matrix > div > div:nth-child(' + index + ')').addClass('greyed');
            });
        },

        moveRow: function(rowId, position) {
            $.fn.swapWith = function(to) {
                return this.each(function() {
                    var copy_to = $(to).clone(true);
                    var copy_from = $(this).clone(true);
                    $(to).replaceWith(copy_from);
                    $(this).replaceWith(copy_to);
                });
            };

            var index = this.getRowPosition(rowId);
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

        },



        getRowPosition: function(rowId) {
            var row_header = $('#'+rowId);
            var i = $('.wa_matrix .wa_row').index(row_header);

            return parseInt(i) + 2;
        },
    }
});
/* jshint ignore:end */