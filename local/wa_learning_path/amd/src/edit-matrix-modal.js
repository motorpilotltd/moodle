/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap', 'local_wa_learning_path/helper', 'local_wa_learning_path/edit-matrix-actions'], function($, bootstrap, helper, actions) {

    "use strict"; // jshint ;_;
    return {
        loadModalRow: function(rowId) {
            var name = $('#'+rowId+'.wa_row .name .tooltip').text();
            $('#myModalRow #delete').attr('data-id', rowId);
            $('#myModalRow #save').attr('data-id', rowId);
            $('#myModalRow #name').val(name);

            $('#myModalRow #position').val(helper.getRowPosition(rowId)-1);

            if($('#' + rowId + ' .row_header').attr('show') == 'yes') {
                $('#myModalRow #visible').prop('checked', true);
            } else {
                $('#myModalRow #visible').prop('checked', false);
            }

            $('#myModalRow').modal('show');

            // Now return a false (negating the link action) to prevent Bootstrap's JS 3.1.1
            // from throwing a 'preventDefault' error due to us overriding the anchor usage.
            return false;
        },

        saveRow: function(rowId) {
            var name = $('#myModalRow #name').val();
            var shortenName = name.replace(/^(.{20}[.]*).*/, "$1");
            if(shortenName.length < name.length) {
                shortenName = shortenName+'...';
            }

            var position = $('#myModalRow #position').val();
            var visible = $('#myModalRow #visible').is(':checked');
            $('#'+rowId+'.wa_row .name .tooltip').text(name);
            $('#'+rowId+'.wa_row .row_header').attr('title', name);
            $('#'+rowId+'.wa_row .name .text').text(shortenName);

            helper.moveRow(rowId, position);
            helper.updateVisibility(rowId, visible, 'row');
            actions.waSaveAjax('updated', 'Row', name);
            $('#myModalRow').modal('hide');
        },

        loadModalColumn: function(columnId) {
            var name = $('.cols #'+columnId+' .name .tooltip').text();
            var description = $('.cols #' + columnId).attr('description');
            var regions = $('.cols #' + columnId + ' .select').val();

            $('#myModalColumn #delete').attr('data-id', columnId);
            $('#myModalColumn #save').attr('data-id', columnId);
            $('#myModalColumn #name').val(name);
            $('#myModalColumn #description').val(description);
            $('#myModalColumn #position').val(helper.getColumnPosition(columnId)-1);
            $('#myModalColumn #regions').val(regions);

            if($('#' + columnId).attr('show') == 'yes') {
                $('#myModalColumn #visible').prop('checked', true);
            } else {
                $('#myModalColumn #visible').prop('checked', false);
            }

            $('#myModalColumn').modal('show');

            // Now return a false (negating the link action) to prevent Bootstrap's JS 3.1.1
            // from throwing a 'preventDefault' error due to us overriding the anchor usage.
            return false;
        },

        saveColumn: function(columnId) {
            var description = $('#myModalColumn #description').val();
            var name = $('#myModalColumn #name').val();
            var shortenName = name.replace(/^(.{18}[.]*).*/, "$1");
            if(shortenName.length < name.length) {
                shortenName = shortenName+'...';
            }

            var position = $('#myModalColumn #position').val();
            var visible = $('#myModalColumn #visible').is(':checked');
            var regions = $('#myModalColumn #regions').val();

            helper.moveColumn(columnId, position);
            helper.updateRegionVisibility(columnId, regions);

            $('.cols #'+columnId).attr('description', description);
            $('.cols #'+columnId+' .name .tooltip').text(name);
            $('.cols #'+columnId).attr('title', name);
            $('.cols #'+columnId+' .name .text').text(shortenName);
            $('.cols #' + columnId + ' .select').val(regions);

            helper.updateVisibility(columnId, visible, 'column');
            actions.waSaveAjax('updated', 'Column', name);
            $('#myModalColumn').modal('hide');
        },

        initialiseRemoveColumn: function(columnId) {
            var name = $('.cols #'+columnId+' .name .tooltip').text();
            var dialog = $("#dialog-confirm").dialog({
                resizable: false,
                height: 200,
                width: 500,
                modal: true,
                autoOpen: false,
                buttons: {
                    "Yes": function () {
                        var col_header = $('#'+columnId);
                        var i = $('.wa_matrix .cols .col_header').index(col_header);
                        var index = parseInt(i) + 2;
                        $('.wa_matrix > div > div:nth-child(' + index + ')').remove();

                        helper.calculateWidthAndHeight();
                        actions.waSaveAjax('deleted', 'Column', name);
                        $(this).dialog("close");
                        $('#myModalColumn').modal('hide');
                    },
                    "No": function () {
                        $(this).dialog("close");
                    },
                }
            });

            dialog.dialog("open");
            return false;
        },

        initialiseRemoveRow: function(rowId) {
            var name = $('#'+rowId+'.wa_row .name .tooltip').text();
            var dialog = $("#dialog-confirm").dialog({
                resizable: false,
                height: 200,
                width: 500,
                modal: true,
                autoOpen: false,
                buttons: {
                    "Yes": function () {
                        $('#' + rowId).remove();
                        actions.waSaveAjax('deleted', 'Row', name);
                        $(this).dialog("close");
                        $('#myModalRow').modal('hide');
                    },
                    "No": function () {
                        $(this).dialog("close");
                    },
                }
            });

            dialog.dialog("open");
            return false;
        },
    }
});
/* jshint ignore:end */