/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap', 'local_wa_learning_path/edit-matrix-modal', 'local_wa_learning_path/helper', 'local_wa_learning_path/edit-matrix-actions', 'core/str'], function($, bootstrap, editMatrixModal, helper, actions, str) {

    "use strict"; // jshint ;_;
    return {
        init: function(columns, rows, maxId, role, roleEnabledActivities, waPlus, waActivities,
                       iconModulesAndObjectives, iconObjectives, iconNoObjectives, iconEdit, lpathid,
                       time, status, canEditMatrix, itemId,limitText, requiredText, selfledText, saveText, cancelText, removeText, closeText,
                       newRowText, newColumnText, returnhash, url
        ) {
            $(document).ready(function($) {
                /********************************************
                 *** Initialise actions-related variables ***
                 *******************************************/

                actions.initVariables(maxId, status, limitText, requiredText, selfledText, saveText, cancelText, removeText, closeText, waActivities);

                /***********************************
                 *** INITIALISE ROWS AND COLUMNS ***
                 **********************************/
                columns.forEach(function(column) {
                    actions.addColumn(column);
                });

                rows.forEach(function(row) {
                    actions.addRow(row, role, roleEnabledActivities, waPlus, lpathid, url);
                });

                actions.waCheckIcons(role, iconModulesAndObjectives, iconObjectives, iconNoObjectives, iconEdit);

                $('#dialog_create_activity input[name="title"]').change( actions.titleValidation() );
                $('#dialog_create_activity input[name="title"]').blur( actions.titleValidation() );

                $('#activitiyform .fstatic').html($('#wa_activity_form').html());

                /******************************************
                 *** Activate adding row/column buttons ***
                 *****************************************/
                if(canEditMatrix) {
                    // Adding new row
                    $("#editing_matrix .add_row").click(function() {
                        var newId = actions.addRow({ show: 1, name: newRowText}, role, roleEnabledActivities, waPlus, lpathid, url);
                        $('div[data-row="'+newId+'"] a[data-toggle="modal"]').on('click', function() {
                            return editMatrixModal.loadModalRow(newId);
                        });
                    });

                    // Adding new column
                    $("#editing_matrix .add_column").click(function() {
                        var newId = actions.addColumn({ show: 1, name: newColumnText, region: ["0"], isnew: 1}, waPlus);
                        $('#' + newId +' a[data-toggle="modal"]').on('click', function() {
                            return editMatrixModal.loadModalColumn(newId);
                        });
                    });
                }

                /*****************************
                 *** Activate form buttons ***
                 ****************************/

                $('#id_submitbutton, #id_submitbutton2, #id_submitbutton3').click(function() {
                    actions.waSave();
                })

                $('#id_acancel').attr('onclick', '');
                $('#id_acancel').click(function() {
                    location.hash = '#cancel';
                    $('#matrixform input[name="returnhash"]').val('');
                    return false;
                })

                $('#id_asubmitbutton').click(function() {
                    $('#matrixform input[name="returnhash"]').val(location.hash);
                    actions.saveActivity();
                    return false;
                })

                $('#activitiyform .wa_tabs a:nth-child(1)').click(function() {
                    $('#activitiyform .wa_tabs a').removeClass('current');
                    $(this).addClass('current');

                    $('#activitiyform .activity.tab').hide();
                    $('#activitiyform .modules.tab').show();
                    return false;
                })

                $('#activitiyform .wa_tabs a:nth-child(2)').click(function() {
                    $('#activitiyform .wa_tabs a').removeClass('current');
                    $(this).addClass('current');

                    $('#activitiyform .modules.tab').hide();
                    $('#activitiyform .activity.tab').show();
                    return false;
                })

                $.expr[":"].contains = $.expr.createPseudo(function(arg) {
                    return function( elem ) {
                        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
                    };
                });

                $('#activitiyform .selectpicker').keypress(function(event) {
                    if (event.keyCode == '13') {
                        return false;
                    }
                });

                $('#activitiyform .selectpicker').keyup(function() {
                    var t = $(this).val();
                    var res = $(this).parent().parent().find('.results');
                    if (t.length) {
                        res.find('a').hide();
                        res.find('a:contains("'+t+'")').show();
                        if (res.find('a:contains("'+t+'")').length) {
                            res.find('span').hide();
                        } else {
                            res.find('span').show();
                        }

                        res.slideDown();
                    } else {
                        res.hide();
                    }
                });

                $('#activitiyform .modules .results a').click(function() {
                    var module = {
                        courseid: $(this).attr('courseid'),
                        html: $(this).html(),
                        methodology: $(this).attr('methodology')
                    };

                    actions.setModule(JSON.stringify(module));
                    $('#activitiyform .modules .results a').removeClass('selected');
                    $(this).addClass('selected');
                    $('#activitiyform .modules button').show();
                    return false;
                });

                $('#activitiyform .activity .results a').click(function() {
                    var activity = {
                        activityid: $(this).attr('activityid'),
                        html: $(this).html()
                    };
                    actions.setToAddActivity(JSON.stringify(activity));
                    $('#activitiyform .activity .results a').removeClass('selected');
                    $(this).addClass('selected');
                    $('#activitiyform .activity button').show();
                    return false;
                });

                $('#activitiyform .modules button.to_add').click(function() {
                    var module = actions.getModule();

                    if (module) {
                        if (actions.addModule(module.courseid, $('.modules.tab').find('select').val(), module.html, module.methodology, 1)) {
                            $('span.add_info').show();
                            setTimeout(function() { $('span.add_info').hide(); }, 3000);
                        }
                    } else {
                        actions.chooseElementToAdd();
                    }

                    return false;
                });

                $('#activitiyform .activity button.to_add').click(function() {
                    var activity = actions.getToAddActivity();

                    if (activity) {
                        if (actions.addActivity(activity.activityid, $('.activity.tab').find('select').val(), activity.html, $('input[name="percent"]:checked').val(), 1)) {
                            $('span.add_info').show();
                            setTimeout(function() { $('span.add_info').hide(); }, 3000);
                        }
                    } else {
                        actions.chooseElementToAdd();
                    }

                    return false;
                });

                $('#dialog_create_activity_id .wa_blue_button').click(function(e) {
                    e.preventDefault();
                    actions.saveNewActivity(lpathid, time);
                    return false;
                });

                $('input[name="activitydraftid"]').val(itemId);

                window.onhashchange = function() {
                    actions.editActivity();
                }

                if(returnhash) {

                } else {
                    location.hash = '#';
                }

                /**********************
                 *** COLUMN EDITION ***
                 *********************/

                // Load column modal
                $('.col_header a[data-toggle="modal"]').on('click', function() {
                    var columnId = $(this).parent().parent().parent().attr('id');
                    return editMatrixModal.loadModalColumn(columnId);
                });

                // Delete column
                $('#myModalColumn #delete').on('click', function() {
                    var columnId = $(this).attr('data-id');
                    editMatrixModal.initialiseRemoveColumn(columnId);
                });

                // Cancel column modal
                $('#myModalColumn #cancel').on('click', function() {
                    $('#myModalColumn').modal('hide');
                });

                // Save column modal
                $('#myModalColumn #save').on('click', function() {
                    var columnId = $(this).attr('data-id');
                    editMatrixModal.saveColumn(columnId);
                });

                $('.activity .add_activity').on('click', function() {
                    actions.createActivity();
                    return false;
                });

                /*******************
                 *** ROW EDITION ***
                 ******************/

                // Load row modal
                $('.row_header a[data-toggle="modal"]').on('click', function() {
                    var rowId = $(this).parent().parent().parent().attr('id');
                    return editMatrixModal.loadModalRow(rowId);
                });

                // Delete row
                $('#myModalRow #delete').on('click', function() {
                    var rowId = $(this).attr('data-id');
                    editMatrixModal.initialiseRemoveRow(rowId);
                });

                // Cancel row modal
                $('#myModalRow #cancel').on('click', function() {
                    $('#myModalRow').modal('hide');
                });

                // Save row modal
                $('#myModalRow #save').on('click', function() {
                    var rowId = $(this).attr('data-id');
                    editMatrixModal.saveRow(rowId);
                });

                /**********************************
                 *** ROLE SELECT INITIALISATION ***
                 **********************************/
                $('select[name="roles"]').change(function() {
                    var role = '';
                    var url = $(this).data('url');

                    if($('#editing_matrix .roles').val()) {
                        role = $('#editing_matrix .roles').val();
                    }

                    if (role > 0)
                        url += '&role=' + role;

                    window.location = url;
                });

                /********************************
                 *** CELL SELECTION HIGHLIGHT ***
                 ********************************/
                $('.cell').on('mouseenter', function() {
                    var column = $(this).data('column');
                    var row = $(this).parent().attr('id');

                    $('.cell').each(function() {
                        $(this).removeClass('cell-highlight');
                        $(this).removeClass('marked-cell');

                        if($(this).data('column') == column) {
                            $(this).addClass('cell-highlight');
                        }

                        if($(this).data('row') == row) {
                            $(this).addClass('cell-highlight');
                        }
                    });

                    $('.col_header').each(function() {
                        $(this).removeClass('cell-highlight');

                        if($(this).attr('id') == column) {
                            $(this).addClass('cell-highlight');
                        }
                    });

                    var row = $(this).data('row');
                    $('.row_header').each(function() {
                        $(this).removeClass('cell-highlight');

                        if($(this).data('row') == row) {
                            $(this).addClass('cell-highlight');
                        }
                    });
                    //
                    // $(this).parent().find('.cell').addClass('cell-highlight');
                    $(this).removeClass('cell-highlight');
                    $(this).addClass('marked-cell');
                });
            });
        },
    }
});
/* jshint ignore:end */