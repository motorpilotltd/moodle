/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap', 'local_wa_learning_path/helper', 'core/str', 'jqueryui'], function($, bootstrap, helper, str, ui) {
    var wa_status = 0, wa_isloading, wa_to_add, wa_to_add_activity;
    var limitText, requiredText, selfledText, saveText, cancelText, removeText, closeText;
    var wa_max_id;
    var wa_rows = [];
    var wa_cols = [];

    var blockNewActivity = false;
    var wa_edit_el = false;

    "use strict"; // jshint ;_;
    return {
        initVariables: function(maxId, status, limitText, requiredText, selfledText, saveText, cancelText, removeText, closeText, waActivities) {

            this.wa_max_id = maxId;
            this.wa_status = status;
            this.wa_isloading = false;
            this.limitText = limitText;
            this.requiredText = requiredText;
            this.selfledText = selfledText;
            this.saveText = saveText;
            this.cancelText = cancelText;
            this.removeText = removeText;
            this.closeText = closeText;

            var checkUp = Object.keys(waActivities);
            if(checkUp.length == 0) {
                this.waActivities = {};
            } else {
                this.waActivities = waActivities;
            }
        },

        deleteConfirmed: function(element) {
            if ($(element).parent().find('> div').length == 1) {
                $(element).parent().find('span').show();
            }

            $(".results a[courseid='"+$(element).find('div').attr('courseid')+"']").removeClass('added');
            $(".results a[activityid='"+$(element).find('div').attr('activityid')+"']").removeClass('added');
            $(element).remove();
        },

        addColumn: function(columnObject, waPlus) {
            // currently we allow only for 15 columns maximum
            if ($('.wa_matrix .col_header').length >= 15) {
                this.showLimitInfo();
            } else {
                if (!columnObject.id) {
                    columnObject.id = this.generateId();
                }

                var columnDomElement = $('.wa_hide .col_header').clone();
                columnDomElement.attr('id', columnObject.id);

                columnDomElement.find('.tooltip').html(columnObject.name);
                columnDomElement.attr('title', columnObject.name);

                var currentHtml = columnDomElement.find('.name').html();
                var name = columnObject.name.replace(/^(.{18}[.]*).*/, "$1");
                if(name.length < columnObject.name.length) {
                    name = name+'...';
                }
                columnDomElement.find('.name .text').html( name );
                if (columnObject.region) {
                    columnDomElement.find('.select').val(columnObject.region);
                    columnObject.region.forEach(function(entry) {
                        columnDomElement.find('span[id="'+entry+'"]').removeClass('hide');
                    });
                }

                if (!columnObject.show) {
                    columnDomElement.attr('show', 'no');
                    columnDomElement.find('.wa_hide2').hide();
                } else {
                    columnDomElement.attr('show', 'yes');
                    columnDomElement.find('.wa_show').hide();
                }

                if (!columnObject.description) {
                    columnDomElement.attr('description', '');
                } else {
                    columnDomElement.attr('description', columnObject.description);
                }

                columnDomElement.attr('data-column', columnObject.id);

                columnDomElement.appendTo($('.wa_matrix .cols'));

                if ($('.wa_matrix .wa_row').length) {
                    $('.wa_matrix').find('> div').each(function () {
                        if (!$(this).hasClass('cols')) {
                            var columnDomElement = $('<div class="cell" data-row="'+ $(this).attr('id') +'" data-column="'+columnObject.id+'">'+waPlus+'</div>');
                            $(columnDomElement).find('a').attr('href', '#'+columnObject.id+'_'+$(this).attr('id'));
                            $(columnDomElement).appendTo($(this));
                        }
                    });
                }
            }

            if(columnObject.isnew) {
                helper.updateRegionVisibility(columnObject.id, columnObject.region);
            }

            helper.setHiddenCells();
            this.setUpMatrixSelection();
            helper.calculateWidthAndHeight();

            return columnObject.id;
        },

        setUpMatrixSelection: function() {
            $('.cell').on('mouseenter', function() {
                var column = $(this).data('column');
                var row = $(this).parent().attr('id');

                $('.wa_matrix div').each(function() {
                    $(this).removeClass('cell-highlight');
                    $(this).removeClass('marked-cell');

                    if($(this).data('column') == column) {
                        $(this).addClass('cell-highlight');
                    }

                    if($(this).data('row') == row) {
                        $(this).addClass('cell-highlight');
                    }
                });

                $(this).removeClass('cell-highlight');
                $(this).addClass('marked-cell');
            });

            $('.cell').on('mouseleave',  function() {
                $('.wa_matrix div').each(function() {
                    $(this).removeClass('marked-cell');
                    $(this).removeClass('cell-highlight');
                });
            });
        },

        addRow: function(rowObject, role, roleEnabledActivities, waPlus, learningPathId, url) {
            if ($('.wa_matrix .wa_row').length >= 15) {
                this.showLimitInfo()
            } else {
                if (!rowObject.id) {
                    rowObject.id = this.generateId();
                }
                var width = $('.wa_matrix').width();
                var htmlRow = $('<div class="wa_row" data-row="'+rowObject.id+'" id="'+rowObject.id+'" />');
                var rowDomElement = $('.wa_hide .row_header').clone();

                rowDomElement.find('.tooltip').html(rowObject.name);
                rowDomElement.attr('title', rowObject.name);

                var currentHtml = rowDomElement.find('.name').html();
                var name = rowObject.name.replace(/^(.{20}[.]*).*/, "$1");
                if(name.length < rowObject.name.length) {
                    name = name+'...';
                }
                rowDomElement.find('.tooltip').html(rowObject.name);
                rowDomElement.find('.name .text').html(name);
                if (!rowObject.show) {
                    rowDomElement.attr('show', 'no');
                    rowDomElement.find('.wa_hide2').hide();
                } else {
                    rowDomElement.attr('show', 'yes');
                    rowDomElement.find('.wa_show').hide();
                }

                rowDomElement.attr('data-row', rowObject.id);

                rowDomElement.appendTo(htmlRow);
                if (!rowObject.show) {
                    rowDomElement.parent().addClass('greyed')
                }

                $('.wa_matrix .col_header').each(function () {
                    var row = $('<div class="cell" data-row="'+rowObject.id+'" data-column="'+$(this).attr('id')+'">'+waPlus+'</div>');

                    if(role == 0) {
                        $(row).find('a').attr('href', '#'+$(this).attr('id')+"_"+rowObject.id);
                    } else {
                        var id = '#'+$(this).attr('id')+"_"+rowObject.id;
                        if ($.inArray(id.toString(), roleEnabledActivities) !== -1) {
                            $(row).addClass('cell-selected');
                            var cellUrl = url+ "&a=edit_activity&id="+learningPathId+"&roleid="+role+"&activityid=" + $(this).attr('id')+"_"+rowObject.id;
                            $(row).find('a').attr('href', cellUrl);
                        } else {
                            $(row).find('a').remove();
                        }
                    }
                    row.appendTo(htmlRow);
                });

                htmlRow.appendTo($('.wa_matrix'));
                $('.wa_matrix').width(width);
                helper.setHiddenCells();

                this.setUpMatrixSelection();
                helper.calculateWidthAndHeight();
                return rowObject.id;
            }
        },

        waCheckIcons: function(role, iconModulesAndObjectives, iconObjectives, iconNoObjectives, iconEdit) {
            for(var property in this.waActivities) {
                var object = this.waActivities[property];
                if(!location.hash || location.hash == property) {
                    if (role == 0) {
                        if (object.positions.essential.length || object.positions.recommended.length || object.positions.elective.length) {
                            $('a[href="'+property+'"] i').attr('class', iconModulesAndObjectives);
                        } else {
                            if (object.content) {
                                $('a[href="'+property+'"] i').attr('class', iconObjectives);
                            } else {
                                $('a[href="'+property+'"] i').attr('class', iconNoObjectives);
                            }
                        }
                    } else {
                        $('a[href="'+property+'"] i').attr('class', iconEdit);
                    }
                }
            }
        },


        setMaxId: function(maxId) {
            this.wa_max_id = maxId;
        },

        generateId: function() {
            this.wa_max_id++;
            return 'g'+this.wa_max_id.toString() ;
        },

        showLimitInfo: function() {
            $("#dialog_info").html(this.limitText);

            var buttons = {
                "Close": function () {
                    $(this).dialog("close");
                },
            }

            $("#dialog_info").dialog({ resizable: false, width: '650px', buttons: buttons });
        },

        setModule: function(module) {
            this.wa_to_add = JSON.parse(module);
        },

        getModule: function() {
            return this.wa_to_add;
        },

        setToAddActivity: function(activity) {
            this.wa_to_add_activity = JSON.parse(activity);
        },

        getToAddActivity: function() {
            return this.wa_to_add_activity;
        },

        saveNewActivity: function(lpathid, time) {
            var module = this;
            if (!this.blockNewActivity) {
                this.blockNewActivity = true;

                if(typeof tinymce !== 'undefined') {
                    tinymce.get("activity_description_id").save();
                }

                var form = $('#dialog_create_activity_id .wa_activity_mform');

                $(form).find('input[name="region"]').attr('name', 'temp');
                var obj = form.serialize();
                $(form).find('input[name="temp"]').attr('name', 'region');

                $.ajax({
                    type: "POST",
                    url: form.prop('action') + "&lpid=" + module.lpathid,
                    data: obj,
                    dataType: "json",
                    success: function (response) {
                        module.blockNewActivity = false;
                        if (response.status && response.status === 'OK') {
                            $('#dialog_create_activity_id').addClass('hide');

                            var a = $('<a date="'+time+'" href="" activityid="' + response.activity.id + '">' + response.activity.title + '</a>').click(function () {
                                var activity = {
                                    activityid: $(this).attr('activityid'),
                                    html: $(this).html()
                                };

                                module.wa_to_add_activity = activity;
                                $('#activitiyform .activity a').removeClass('selected');
                                $(this).addClass('selected');
                                $('#activitiyform .activity button').show();
                                return false;
                            });

                            $('.activity.tab .selectpicker').val('');
                            $('.activity.tab .results a').hide();
                            $('.activity.tab .results').append(a);
                            $('.activity.tab .results').slideDown().find('span').hide();

                            return false;
                        } else if(response.status && response.status === 'ERROR'){
                            var field;
                            for ( field in response.errors) {

                                var msg = $('<span>').addClass('error').html(response.errors[field]);
                                $('#dialog_create_activity [name="' + field + '"]').parent().find('span.error, br').remove();
                                $('#dialog_create_activity [name="' + field + '"]').parent().prepend(msg);
                            }
                            $("#dialog_create_activity").scrollTop(0);
                            return false;
                        }else {
                            alert('ERROR');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        this.blockNewActivity = false;
                    }
                });
            }
        },

        titleValidation: function() {
            var obj = $('#dialog_create_activity input[name="title"]');
            $(obj).parent().find('span.error, br').remove();
            if($(obj).val() === '') {
                var msg = $('<span>').addClass('error').html( this.requiredText );
                $(obj).parent().prepend(msg);
            }
        },


        activityClearForm: function() {
            $('.wa_activity_mform input[name="title"]').val('');
            $('.wa_activity_mform select[name="region[]"]').val('0');
            $('.wa_activity_mform select[name="type"]').val('video');
            $('.wa_activity_mform #id_description_editoreditable').html('');
            $('.wa_activity_mform input[name="provider"]').val( this.selfledText );
            $('.wa_activity_mform input[name="enablecdp"]').prop( "checked", true );
            $('.wa_activity_mform select[name="learningmethod"]').val('SELF-LED');
            $('.wa_activity_mform input[name="duration"]').val('');
            $('.wa_activity_mform select[name="unit"]').val('MIN');
            $('.wa_activity_mform select[name="subject"]').val('PD');
            $('.wa_activity_mform #id_learningdescriptioneditable').html('');
        },

        createActivity: function() {
            var buttons = [
                {
                    text: this.saveText,
                    class: "wa_blue_button",
                    click: function() {
                        this.saveNewActivity();
                    }
                },
                {
                    text: this.cancelText,
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                },
            ];

            this.activityClearForm();
            $('#dialog_create_activity_id').removeClass('hide');

            return false;
        },

        addPosition: function(pos, section) {
            if (section == 1) { // <?php echo WA_LEARNING_PATH_ESSENTIAL ?>
                $('#activitiyform .added .essential span').hide();
                $('#activitiyform .added .essential').append(pos);
            } else
            if (section == 2) { // <?php echo WA_LEARNING_PATH_RECOMMENDED ?>
                $('#activitiyform .added .recommended span').hide();
                $('#activitiyform .added .recommended').append(pos);
            } else {
                $('#activitiyform .added .elective span').hide();
                $('#activitiyform .added .elective').append(pos);
            }
        },

        getDateForNew: function() {
            if (this.wa_status == 2) { // WA_LEARNING_PATH_PUBLISH
                var date = new Date();
                return Math.round(date.getTime() / 1000);
            } else {
                return 0;
            }
        },

        addModule: function(id, section, name, methodology, isnew) {
            var date = isnew == 1 ? this.getDateForNew() : isnew;
            if ($('#activitiyform .added div[courseid="'+id+'"]').length > 0) {
                this.alreadyAddedMessage();
            } else {
                var pos = $('<div id="module'+id+'"><div class="module" date="' + date + '" courseid="' + id + '"><div class="methodology">' + methodology + '</div><div class="name">' + name + '</div><div><button class="removeModule">'+this.removeText+'</button></div></div></div>');
                $('.results a[courseid="'+id+'"]').addClass('added');
                this.addPosition(pos, section);
                this.initialiseRemove('module', id);
                return true;
            }
        },

        addActivity: function(id, section, name, percent, isnew) {
            if ($('#activitiyform .added div[activityid="'+id+'"]').length > 0) {
                this.alreadyAddedMessage();
            } else {
                var date = isnew == 1 ? this.getDateForNew() : isnew;
                var pos = $('<div id="activity'+id+'"><div class="activity" date="' + date + '" activityid="' + id + '" percent="' + percent + '"><div class="title">' + name + ' (' + percent + ' %)' + '</div><div><button class="removeModule">'+this.removeText+'</button></div></div></div>');
                $('.results a[activityid="'+id+'"]').addClass('added');
                this.addPosition(pos, section);
                this.initialiseRemove('activity', id);
                return true;
            }
        },

        initialiseRemove: function(type, id) {
            var object = this;
            var element = $('#' + type + id);
            $('#' + type + id + ' .removeModule').on('click', function() {
                // object.removeModule(element);
                var dialog = $("#dialog-confirm").dialog({
                    resizable: false,
                    height: 200,
                    width: 500,
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        "Yes": function () {
                            object.deleteConfirmed(element);
                            $(this).dialog("close");
                        },
                        "No": function () {
                            $(this).dialog("close");
                        },
                    }
                });

                dialog.dialog("open");
                return false;
            });
        },

        waSave: function() {
            var cols = [];
            $('.wa_matrix .col_header').each(function() {
                var col = { id: $(this).attr('id'), region: $(this).find('.select').val(), name: $(this).find('.name .tooltip').html(), description: $(this).attr('description'), show: $(this).attr('show') == 'yes' }
                cols.push(col);
            })

            var rows = [];
            $('.wa_matrix .row_header').each(function() {
                var row = { id: $(this).parent().attr('id'), name: $(this).find('.name .tooltip').html(), show: $(this).attr('show') == 'yes' }
                rows.push(row);
            })

            var data = { cols: cols, rows: rows, activities: this.waActivities, max_id: this.wa_max_id, itemid: $('input[name="description_editor[itemid]"]').val(), };

            $('#matrixform input[name="matrix"]').val(JSON.stringify(data));
        },

        waSaveAjax: function(action,type, name) {
            this.waSave();
            var matrixData =    $('#matrixform input[name="matrix"]').val();
            var id = $('#matrixform input[name="id"]').val();
            var url = $('#matrixform').attr('action') + '?c=admin&a=save_matrix_ajax&id=' + id;

            $.ajax({
                type: "POST",
                url: url,
                data: {matrix: matrixData},
                success: function (response) {
                    // Path matrix has been saved
                    noticeDiv = $('.alert-success');
                    var time = new Date().toUTCString();
                    if(noticeDiv.length) {
                        noticeDiv.text(type + ' ' + name + ' has been ' + action);
                    } else {
                        var notice = $('<div class="alert alert-success">' + type + ' ' + name + ' has been ' + action + '</div>');
                        notice.prependTo($('#editing_matrix'));
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                }
            });
        },

        alreadyAddedMessage: function() {
            var buttons = [{
                text: this.closeText,
                click: function() {
                    $( this ).dialog( "close" );
                }
            }];

            $("#dialog-message").dialog({
                resizable: false,
                height: 180,
                width: 450,
                modal: false,
                buttons: buttons });
        },

        chooseElementToAdd: function() {
            var buttons = [{
                text: this.closeText,
                click: function() {
                    $( this ).dialog( "close" );
                }
            }];

            $("#dialog-message2").dialog({
                resizable: false,
                height: 180,
                width: 450,
                modal: false,
                buttons: buttons });
        },

        editActivity: function() {
            var hash = location.hash;

            if (hash == '#save') {
                $('#id_submitbutton').click();
            } else
            if (!hash || hash == '#cancel' || hash == '#') {
                $('#editing_activity').hide();
                $('#editing_matrix').fadeIn();
            } else {
                $('.selectpicker').val('');

                var plus = $('.wa_matrix a[href="' + hash + '"]');
                var row = $(plus).parents('.wa_row').find('.name .tooltip').html();

                var i = $(plus).parents('.cell').index() + 1;
                var col = $('.wa_matrix .cols div:nth-child(' + i + ') .name .tooltip').html();

                $('#editing_activity h2').html(row+', '+col);

                $('#editing_matrix').hide();
                $('#editing_activity').fadeIn();

                var content = '';
                if (this.waActivities[location.hash]) {
                    content = this.waActivities[location.hash].content;
                }

                $('#id_content_editoreditable').html(content);
                $('#id_content_editor_ifr').contents().find('body').html(content);

                $('#activitiyform .essential > div').remove();
                $('#activitiyform .recommended > div').remove();
                $('#activitiyform .elective > div').remove();
                $('#activitiyform .added span').show();

                var helpObject = this;
                if (this.waActivities[location.hash]) {
                    if (this.waActivities[location.hash].positions.essential.length) {
                        $('#activitiyform .added .essential span').hide();
                        $.each(this.waActivities[location.hash].positions.essential, function () {
                            if (this.type == 'module') {
                                var a = $('.modules a[courseid="' + this.id + '"]');
                                helpObject.addModule(this.id, 1, a.html(), this.methodology, this.date);
                            } else {
                                var a = $('.activity a[activityid="' + this.id + '"]');
                                helpObject.addActivity(this.id, 1, a.html(), this.percent, this.date);
                            }
                        })
                    }

                    if (this.waActivities[location.hash].positions.recommended.length) {
                        $('#activitiyform .added .recommended span').hide();
                        $.each(this.waActivities[location.hash].positions.recommended, function () {
                            if (this.type == 'module') {
                                var a = $('.modules a[courseid="' + this.id + '"]');
                                helpObject.addModule(this.id, 2, a.html(), this.methodology, this.date);
                            } else {
                                var a = $('.activity a[activityid="' + this.id + '"]');
                                helpObject.addActivity(this.id, 2, a.html(), this.percent, this.date);
                            }
                        })
                    }

                    if (this.waActivities[location.hash].positions.elective.length) {
                        $('#activitiyform .added .elective span').hide();
                        $.each(this.waActivities[location.hash].positions.elective, function () {
                            if (this.type == 'module') {
                                var a = $('.modules a[courseid="' + this.id + '"]');
                                helpObject.addModule(this.id, 3, a.html(), this.methodology, this.date);
                            } else {
                                var a = $('.activity a[activityid="' + this.id + '"]');
                                helpObject.addActivity(this.id, 3, a.html(), this.percent, this.date);
                            }
                        })
                    }
                }
            }
        },

        saveActivity: function() {
            var essential = [];
            $('#activitiyform .essential > div').each(function() {
                var el = $(this).find('> div');
                if ($(el).hasClass('module')) {
                    essential.push({ type: 'module', id: $(el).attr('courseid'), date: $(el).attr('date'), methodology: $(el).find('.methodology').html() });
                } else {
                    essential.push({ type: 'activity', id: $(el).attr('activityid'), date: $(el).attr('date'), percent: $(el).attr('percent'), });
                }
            });

            var recommended = [];
            $('#activitiyform .recommended > div').each(function() {
                var el = $(this).find('> div');
                if ($(el).hasClass('module')) {
                    recommended.push({ type: 'module', id: $(el).attr('courseid'), date: $(el).attr('date'), methodology: $(el).find('.methodology').html() });
                } else {
                    recommended.push({ type: 'activity', id: $(el).attr('activityid'), date: $(el).attr('date'), percent: $(el).attr('percent'), });
                }
            });

            var elective = [];
            $('#activitiyform .elective > div').each(function() {
                var el = $(this).find('> div');
                if ($(el).hasClass('module')) {
                    elective.push({ type: 'module', id: $(el).attr('courseid'), date: $(el).attr('date'), methodology: $(el).find('.methodology').html() });
                } else {
                    elective.push({ type: 'activity', id: $(el).attr('activityid'), date: $(el).attr('date'), percent: $(el).attr('percent'), });
                }
            });


            var content = '';
            if ($('#id_content_editoreditable').length) {
                content = $('#id_content_editoreditable').html();
            }

            if ($('#id_content_editor_ifr').length) {
                content = $('#id_content_editor_ifr').contents().find('body').html();
            }

            this.waActivities[location.hash] = {
                content: content,
                positions: {
                    essential: essential,
                    recommended: recommended,
                    elective: elective
                }
            }

            this.waSave();
            location.hash = '#save';
        },

    }
});
/* jshint ignore:end */