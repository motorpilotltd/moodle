<?php if ($this->id && \wa_learning_path\lib\has_capability('editlearningmatrix')): ?>
    <div class="wa_tabs">
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit&id='.$this->id)); ?>"><?php echo $this->get_string('tab_introduction') ?></a>
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit_matrix&id='.$this->id)); ?>" class="current"><?php echo $this->get_string('tab_matrix') ?></a>
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit_subscriptions&id='.$this->id)); ?>"><?php echo $this->get_string('tab_subscriptions') ?></a>
    </div>
<?php endif; ?>

<div id="editing_matrix">
    <?php
    $this->display_error($this->get_flash_massage('success'), 'success');
    $this->display_error($this->get_flash_massage('error'), 'error');
    $this->display_error($this->get_flash_massage('other'), 'other');

    global $OUTPUT;
    ?>

    <div class="legend_container">
        <div class="legend">
            <?php echo $this->get_string('key') ?>:<br />
            <?php echo html_writer::empty_tag('img', array('src' => new \moodle_url('/local/wa_learning_path/img/plus1.png'), 'alt' => $this->get_string('up'), 'class' => '')) ?>
            <?php echo $this->get_string('no_objective_defined') ?>
            <br />
            <?php echo html_writer::empty_tag('img', array('src' => new \moodle_url('/local/wa_learning_path/img/plus2.png'), 'alt' => $this->get_string('up'), 'class' => '')) ?>
            <?php echo $this->get_string('objectives_defined') ?>
            <br />
            <?php echo html_writer::empty_tag('img', array('src' => new \moodle_url('/local/wa_learning_path/img/plus3.png'), 'alt' => $this->get_string('up'), 'class' => '')) ?>
            <?php echo $this->get_string('both_defined') ?>
        </div>
    </div>

    <br />
    <br />
    <br />
    <br />
    <br />
    <?php if (\wa_learning_path\lib\has_capability('editmatrixgrid')): ?>
    <div class="buttons">
        <button class="add_row">
            <?php echo $this->get_string('add_row') ?>
        </button>

        <button class="add_column">
            <?php echo $this->get_string('add_column') ?>
        </button>
    </div>
    <?php endif; ?>

    <div class="wa_matrix">
        <div class="cols">
            <div class="empty"></div>
        </div>
    </div>

    <?php if (\wa_learning_path\lib\has_capability('editmatrixgrid')): ?>
        <div class="buttons">
            <button class="add_row">
                <?php echo $this->get_string('add_row') ?>
            </button>

            <button class="add_column">
                <?php echo $this->get_string('add_column') ?>
            </button>
        </div>
    <?php endif; ?>

    <div class="wa_hide">
        <div class="col_header">
            <div class="m3">
                <select class="select" multiple="1">
                    <?php $regions = \wa_learning_path\lib\get_regions(); foreach($regions as $k => $region): ?>
                    <option value="<?php echo $k ?>" <?php if ($k == 0) echo "selected='1'"; ?>><?php echo $region ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <?php
                if (\wa_learning_path\lib\has_capability('editmatrixgrid')):

                    echo html_writer::link(new \moodle_url($this->url, array('a' => 'edit', 'id' => $this->id)),
                        html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/edit'), 'alt' => $this->get_string('edit'), 'class' => 'iconsmall')),
                        array('title' => $this->get_string('edit'), 'class' => 'btn', 'onclick' => "wa_edit_name(this); return false;"));
                    echo html_writer::link(new \moodle_url($this->url, array('a' => 'hide', 'id' => $this->id)),
                        html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/hide'), 'alt' => $this->get_string('hide'), 'class' => 'iconsmall')),
                        array('title' => $this->get_string('hide'), 'class' => 'btn wa_hide2', 'onclick' => "wa_toggle(this); return false;"));
                    echo html_writer::link(new \moodle_url($this->url, array('a' => 'show', 'id' => $this->id)),
                        html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/show'), 'alt' => $this->get_string('show'), 'class' => 'iconsmall')),
                        array('title' => $this->get_string('show'), 'class' => 'btn wa_show', 'onclick' => "wa_toggle(this); return false;"));
                    echo html_writer::link(new \moodle_url($this->url, array('a' => 'delete', 'id' => $this->id)),
                        html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/delete'), 'alt' => $this->get_string('delete'), 'class' => 'iconsmall')),
                        array('title' => $this->get_string('delete'), 'class' => 'btn', 'onclick' => "wa_delete_col(this); return false;"));

                    echo html_writer::link(new \moodle_url($this->url, array('a' => 'left', 'id' => $this->id)),
                        html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/left'), 'alt' => $this->get_string('left'), 'class' => 'iconsmall')),
                        array('title' => $this->get_string('left'), 'class' => 'btn left', 'onclick' => "wa_left_col(this); return false;"));
                    echo html_writer::link(new \moodle_url($this->url, array('a' => 'right', 'id' => $this->id)),
                        html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/right'), 'alt' => $this->get_string('right'), 'class' => 'iconsmall')),
                        array('title' => $this->get_string('right'), 'class' => 'btn right', 'onclick' => "wa_right_col(this); return false;"));

                endif;
                ?>
                <br />
                <span class="name">NAME</span>
            </div>
        </div>

        <div class="row_header">
            <div class="name">
                NAME
            </div>
            <?php if (\wa_learning_path\lib\has_capability('editmatrixgrid')): ?>
            <div>
                <?php
                echo html_writer::link(new \moodle_url($this->url, array('a' => 'up', 'id' => $this->id)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/up'), 'alt' => $this->get_string('up'), 'class' => 'iconsmall')),
                    array('title' => $this->get_string('up'), 'class' => 'btn up', 'onclick' => "wa_up_row(this); return false;"));
                echo html_writer::link(new \moodle_url($this->url, array('a' => 'down', 'id' => $this->id)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/down'), 'alt' => $this->get_string('down'), 'class' => 'iconsmall')),
                    array('title' => $this->get_string('down'), 'class' => 'btn down', 'onclick' => "wa_down_row(this); return false;"));

                echo html_writer::link(new \moodle_url($this->url, array('a' => 'edit', 'id' => $this->id)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/edit'), 'alt' => $this->get_string('edit'), 'class' => 'iconsmall')),
                    array('title' => $this->get_string('edit'), 'class' => 'btn edit', 'onclick' => "wa_edit_name(this); return false;"));
                echo html_writer::link(new \moodle_url($this->url, array('a' => 'hide', 'id' => $this->id)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/hide'), 'alt' => $this->get_string('hide'), 'class' => 'iconsmall')),
                    array('title' => $this->get_string('hide'), 'class' => 'btn wa_hide2', 'onclick' => "wa_toggle(this); return false;"));
                echo html_writer::link(new \moodle_url($this->url, array('a' => 'show', 'id' => $this->id)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/show'), 'alt' => $this->get_string('show'), 'class' => 'iconsmall')),
                    array('title' => $this->get_string('show'), 'class' => 'btn wa_show ', 'onclick' => "wa_toggle(this); return false;"));
                echo html_writer::link(new \moodle_url($this->url, array('a' => 'delete', 'id' => $this->id)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/delete'), 'alt' => $this->get_string('delete'), 'class' => 'iconsmall')),
                    array('title' => $this->get_string('delete'), 'class' => 'btn delete', 'onclick' => "wa_delete_row(this); return false;"));
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $this->form->display();
    ?>
</div>

<div id="editing_activity" class="wa_hide">
    <h2></h2>
    <?php $this->activityform->display(); ?>
</div>

<div id="test_activity">

</div>

<?php
$time = time();
//$this->form->display();
?>
<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable hide" tabindex="-1" role="dialog" aria-describedby="dialog_create_activity" aria-labelledby="ui-id-2" id="dialog_create_activity_id" style="height: auto; width: 750px; /*top: 374px; left: 558px;*/ display: block;">
	<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix ui-draggable-handle">
		<span id="ui-id-2" class="ui-dialog-title"><?php echo $this->get_string('header_learning_add_activity') ?></span>
		<button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" title="<?php echo $this->get_string('close') ?>"  onclick="$('#dialog_create_activity_id').addClass('hide');">
			<span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text"><?php echo $this->get_string('close') ?></span>
		</button>
	</div>
	<div id="dialog_create_activity" class="dialog ui-dialog-content ui-widget-content" style="width: auto; min-height: 0px; max-height: none; height: 584.4px;">
	<div id="form-container"><?php $this->activity_form->display(); ?></div>
	</div>
	<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
		<div class="ui-dialog-buttonset">
			<button type="button" class="wa_blue_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" onclick="wa_save_new_activity(); return false;">
				<span class="ui-button-text"><?php echo $this->get_string('save') ?></span>
			</button>
			<button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" onclick="$('#dialog_create_activity_id').addClass('hide');">
				<span class="ui-button-text"><?php echo $this->get_string('cancel') ?></span>
			</button>
		</div>
	</div>
</div>
<div class="hide">
    <div id="dialog_info" title="<?php echo $this->get_string('info') ?>" class="dialog"></div>
    <div id="dialog_edit" title="<?php echo $this->get_string('edit_name') ?>" class="dialog">
        <input style='width: 100%;' type="text" name="name" value="" />
    </div>
</div>

<div id="wa_activity_form" class="hide">
    <div class="wa_tabs">
        <a href="" class="current"><?php echo $this->get_string('module') ?></a>
        <a href=""><?php echo $this->get_string('activity') ?></a>
    </div>
    <div class="modules tab">
        <div class="marg">
            <input class="selectpicker" placeholder="<?php echo $this->get_string('start_typing') ?>" value="" />
        </div>

        <div class="results wa_hide">
            <span><?php echo $this->get_string('no_results') ?></span>
            <?php foreach($this->modules as $module): ?>
                <a date="<?php echo $time ?>" courseid='<?php echo $module->id ?>' methodology='<?php echo @$module->methodology ?>' href=""><?php echo $module->fullname ?> <?php echo $module->region ? '('.$this->get_string('region').' '.$module->region.')' : ''; ?></a>
            <?php endforeach; ?>
        </div>

        <?php echo $this->get_string('tab_heading') ?> <select>
            <option value="<?php echo WA_LEARNING_PATH_ESSENTIAL ?>"><?php echo $this->get_string('essential') ?></option>
            <option value="<?php echo WA_LEARNING_PATH_RECOMMENDED ?>"><?php echo $this->get_string('recommended') ?></option>
            <option value="<?php echo WA_LEARNING_PATH_ELECTIVE ?>"><?php echo $this->get_string('elective') ?></option>
        </select>
        <br />
        <br />
        <button class="to_add"><?php echo $this->get_string('add') ?></button> <span class="add_info wa_hide"><?php echo $this->get_string('element_has_been_added') ?></span>
    </div>

    <div class="activity tab wa_hide">
        <div class="marg">
            <input class="selectpicker" placeholder="<?php echo $this->get_string('start_typing_activity') ?>" value="" />
            <?php /* if (\wa_learning_path\lib\has_capability('addactivity')): */ ?>
                <button class="add_activity" onclick="wa_create_new_activity(); return false;">
                    <?php echo $this->get_string('create_new_activity') ?>
                </button>
            <?php /* endif; */ ?>
        </div>

        <div class="results wa_hide">
            <span><?php echo $this->get_string('no_results') ?></span>
            <?php foreach($this->activities_list as $activity): ?>
                <a date="<?php echo $time ?>" activityid='<?php echo $activity->id ?>' href=""><?php echo $activity->title ?></a>
            <?php endforeach; ?>
        </div>

        <?php echo $this->get_string('tab_heading') ?> <select>
            <option value="<?php echo WA_LEARNING_PATH_ESSENTIAL ?>"><?php echo $this->get_string('essential') ?></option>
            <option value="<?php echo WA_LEARNING_PATH_RECOMMENDED ?>"><?php echo $this->get_string('recommended') ?></option>
            <option value="<?php echo WA_LEARNING_PATH_ELECTIVE ?>"><?php echo $this->get_string('elective') ?></option>
        </select>
        &nbsp;&nbsp;&nbsp;
        <input type="radio" name="percent" value="70" checked="checked" /> 70%
        &nbsp;&nbsp;&nbsp;
        <input type="radio" name="percent" value="20" /> 20%
        &nbsp;&nbsp;&nbsp;
        <input type="radio" name="percent" value="10" /> 10%
        <br />
        <br />
        <button class="to_add"><?php echo $this->get_string('add') ?></button> <span class="add_info wa_hide"><?php echo $this->get_string('element_has_been_added') ?></span>
    </div>
    <br />

    <div class="added">
        <b><?php echo $this->get_string('essential') ?></b><br />
        <div class="essential">
            <span><?php echo $this->get_string('none') ?></span>
        </div>

        <b><?php echo $this->get_string('recommended') ?></b><br />
        <div class="recommended">
            <span><?php echo $this->get_string('none') ?></span>
        </div>

        <b><?php echo $this->get_string('elective') ?></b><br />
        <div class="elective">
            <span><?php echo $this->get_string('none') ?></span>
        </div>
    </div>
</div>

<div id="dialog-confirm" style="display: none;" title="<?php echo $this->get_string('confirmation') ?>">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo $this->get_string('confirm_element') ?></p>
</div>

<div id="dialog-message" style="display: none;" title="<?php echo $this->get_string('information') ?>">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo $this->get_string('element_already_added') ?></p>
</div>

<div id="dialog-message2" style="display: none;" title="<?php echo $this->get_string('information') ?>">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo $this->get_string('choose_element') ?></p>
</div>

<script>
    var dialog_confirm, delete_el, delete_type;
    var wa_status = 0, wa_isloading, wa_to_add, wa_to_add_activity;

    var wa_rows = [];
    var wa_cols = [];

    var wa_block_new_activity = false;
    wa_save_new_activity = function() {
        if (!wa_block_new_activity) {
            wa_block_new_activity = true;

            if(typeof tinymce !== 'undefined') {
                tinymce.get("activity_description_id").save();
            }

            var form = $('#dialog_create_activity_id .wa_activity_mform');

            $(form).find('input[name="region"]').attr('name', 'temp');
            var obj = form.serialize();
            $(form).find('input[name="temp"]').attr('name', 'region');


            $.ajax({
                type: "POST",
                url: form.prop('action') + "&lpid=<?php echo $this->id ?>",
                data: obj,
                dataType: "json",
                success: function (response) {
                    wa_block_new_activity = false;
                    if (response.status && response.status === 'OK') {
                        $('#dialog_create_activity_id').addClass('hide');

                        var a = $('<a date="<?php echo $time ?>" href="" activityid="' + response.activity.id + '">' + response.activity.title + '</a>').click(function () {
                            wa_to_add_activity = $(this);
                            $('#activitiyform .activity a').removeClass('selected');
                            $(this).addClass('selected');
                            $('#activitiyform .activity button').show();
                            return false;
                        })

                        $('.activity.tab .selectpicker').val('');
                        $('.activity.tab .results a').hide();
                        $('.activity.tab .results').append(a);
                        $('.activity.tab .results').slideDown().find('span').hide();
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
                    wa_block_new_activity = false;
                }
            });
        }
    };

	title_validation = function() {
		var obj = $('#dialog_create_activity input[name="title"]');
			$(obj).parent().find('span.error, br').remove();
		if($(obj).val() === '') {
			var msg = $('<span>').addClass('error').html('<?php echo get_string('required', 'moodle') ?>');
			$(obj).parent().prepend(msg);
		}
	};

	$('#dialog_create_activity input[name="title"]').change( title_validation );
	$('#dialog_create_activity input[name="title"]').blur( title_validation );

    wa_activity_clear_form = function() {
        $('.wa_activity_mform input[name="title"]').val('');
        $('.wa_activity_mform select[name="region[]"]').val('0');
        $('.wa_activity_mform select[name="type"]').val('<?php echo \wa_learning_path\model\activity::TYPE_VIDEO ?>');
        $('.wa_activity_mform #id_description_editoreditable').html('');
        $('.wa_activity_mform input[name="provider"]').val('<?php echo $this->get_string('self-led_learning') ?>');
        $('.wa_activity_mform input[name="enablecdp"]').prop( "checked", false );
        $('.wa_activity_mform select[name="learningmethod"]').val('SELF-LED');
        $('.wa_activity_mform input[name="duration"]').val('');
        $('.wa_activity_mform select[name="unit"]').val('MIN');
        $('.wa_activity_mform select[name="subject"]').val('PD');
        $('.wa_activity_mform #id_learningdescriptioneditable').html('');
    };

    wa_create_new_activity = function() {
        var buttons = [
             {
                text: "<?php echo $this->get_string('save') ?>",
                class: "wa_blue_button",
                click: function() {
                    wa_save_new_activity();
                }
            },
            {
                text: "<?php echo $this->get_string('cancel') ?>",
                click: function() {
                    $( this ).dialog( "close" );
                }
            },
        ];

        wa_activity_clear_form();
        $('#dialog_create_activity_id').removeClass('hide');

        return false;
    }

    var wa_plus = '<?php
        echo html_writer::link('#id',
        html_writer::empty_tag('img', array('src' => new \moodle_url('/local/wa_learning_path/img/plus1.png'), 'alt' => $this->get_string('edit'), 'class' => '')),
        array('title' => $this->get_string('up'), 'class' => '', 'onclick' => ""));
        ?>';

    wa_add_col = function(col) {
        if ($('.wa_matrix .col_header').length >= 15) {
            wa_show_limit_info()
        } else {
            if (!col.id) {
                col.id = wa_gen_id();
            }

            var r = $('.wa_hide .col_header').clone();
            r.attr('id', col.id);
            r.find('.name').html(col.name);
            if (col.region) {
                r.find('.select').val(col.region);
            }

            if (!col.show) {
                r.attr('show', 'no');
                r.find('.wa_hide2').hide();
            } else {
                r.attr('show', 'yes');
                r.find('.wa_show').hide();
            }

            r.appendTo($('.wa_matrix .cols'));

            if ($('.wa_matrix .wa_row').length) {
                $('.wa_matrix').find('> div').each(function () {
                    if (!$(this).hasClass('cols')) {
                        var r = $('<div class="cell">'+wa_plus+'</div>');
                        $(r).find('a').attr('href', '#'+col.id+'_'+$(this).attr('id'));
                        $(r).appendTo($(this));
                    }
                });
            }
        }

        wa_fix_left_right_buttons();
        wa_set_greyed_cells();
    }

    wa_add_row = function(row) {
        if ($('.wa_matrix .wa_row').length >= 15) {
            wa_show_limit_info()
        } else {
            if (!row.id) {
                row.id = wa_gen_id();
            }

            var html_row = $('<div class="wa_row" id="'+row.id+'" />');
            var html_row_header = $('.wa_hide .row_header').clone();
            html_row_header.find('.name').html(row.name);
            if (!row.show) {
                html_row_header.attr('show', 'no');
                html_row_header.find('.wa_hide2').hide();
            } else {
                html_row_header.attr('show', 'yes');
                html_row_header.find('.wa_show').hide();
            }

            html_row_header.appendTo(html_row);
            if (!row.show) {
                html_row_header.parent().addClass('greyed')
            }

            $('.wa_matrix .col_header').each(function () {
                var r = $('<div class="cell">'+wa_plus+'</div>');
                $(r).find('a').attr('href', '#'+$(this).attr('id')+"_"+row.id);
                r.appendTo(html_row);
            })

            html_row.appendTo($('.wa_matrix'));
            wa_fix_up_down_buttons();
            wa_set_greyed_cells();
        }
    }

    wa_set_greyed_cells = function() {
        $('.wa_matrix .cell').removeClass('greyed');

        $('.wa_matrix .col_header[show="no"]').each(function () {
            var i = $('.wa_matrix .cols .col_header').index(this);
            var index = parseInt(i) + 2;
            $('.wa_matrix > div > div:nth-child(' + index + ')').addClass('greyed');
        });
    }

    jQuery.fn.swapWith = function(to) {
        return this.each(function() {
            var copy_to = $(to).clone(true);
            var copy_from = $(this).clone(true);
            $(to).replaceWith(copy_from);
            $(this).replaceWith(copy_to);
        });
    };

    wa_delete_col = function(el) {
        delete_el = el;
        delete_type = 'col';
        dialog_confirm.dialog("open");
    }

    wa_delete_confirmed = function(el) {
        if (delete_type == 'col') {
            var col_header = $(el).parent().parent();
            var i = $('.wa_matrix .cols .col_header').index(col_header);
            var index = parseInt(i) + 2;

            $('.wa_matrix > div > div:nth-child(' + index + ')').remove();
        }

        if (delete_type == 'row') {
            $(el).parent().parent().parent().remove();
        }

        if (delete_type == 'activity') {
            if ($(el).parent().parent().parent().parent().find('> div').length == 1) {
                $(el).parent().parent().parent().parent().find('span').show();
            }

            $(".results a[courseid='"+$(el).parent().parent().attr('courseid')+"']").removeClass('added');
            $(".results a[activityid='"+$(el).parent().parent().attr('activityid')+"']").removeClass('added');
            $(el).parent().parent().parent().remove();
        }
    }

    wa_delete_row = function(el) {
        delete_el = el;
        delete_type = 'row';
        dialog_confirm.dialog("open");
    }

    wa_fix_up_down_buttons = function() {
        if (!wa_isloading) {
            $('.wa_matrix .wa_row .up').show();
            $('.wa_matrix .wa_row .down').show();

            $('.wa_matrix .wa_row:nth-child(2) .up').hide();
            $('.wa_matrix .wa_row:last-child .down').hide();
        }
    }

    wa_fix_left_right_buttons = function() {
        if (!wa_isloading) {
            $('.wa_matrix .cols .left').show();
            $('.wa_matrix .cols .right').show();

            $('.wa_matrix .cols > div:nth-child(2) .left').hide();
            $('.wa_matrix .cols > div:last-child .right').hide();
        }
    }

    wa_up_row = function(element) {
        $(element).parent().parent().parent().swapWith($(element).parent().parent().parent().prev());
        wa_fix_up_down_buttons();
    }

    wa_down_row = function(element) {
        $(element).parent().parent().parent().swapWith($(element).parent().parent().parent().next());
        wa_fix_up_down_buttons();
    }

    wa_left_col = function(element) {
        var i = $(element).parent().parent().index();

        $('.wa_matrix > div').each(function (j) {
            if (!j) {
                var s1 = $(this).find('> div:nth-child('+(i + 1)+') .select').val();
                var s2 = $(this).find('> div:nth-child('+(i)+') .select').val();
            }

            $(this).find('> div:nth-child('+(i + 1)+')').swapWith($(this).find('> div:nth-child('+(i)+')'));

            if (!j) {
                $(this).find('> div:nth-child('+(i + 1)+') .select').val(s2);
                $(this).find('> div:nth-child('+(i)+') .select').val(s1);
            }
        })

        wa_fix_left_right_buttons();
    }

    wa_right_col = function(element) {
        var i = $(element).parent().parent().index();

        $('.wa_matrix > div').each(function (j) {
            if (!j) {
                var s1 = $(this).find('> div:nth-child('+(i + 1)+') .select').val();
                var s2 = $(this).find('> div:nth-child('+(i + 2)+') .select').val();
            }

            $(this).find('> div:nth-child('+(i + 1)+')').swapWith($(this).find('> div:nth-child('+(i + 2)+')'));

            if (!j) {
                $(this).find('> div:nth-child('+(i + 1)+') .select').val(s2);
                $(this).find('> div:nth-child('+(i + 2)+') .select').val(s1);
            }
        })

        wa_fix_left_right_buttons();
    }

    wa_toggle = function(el) {
        $(el).hide();

        if ($(el).hasClass('wa_show')) {
            $(el).parents('.wa_row').removeClass('greyed');
            $(el).parent().parent().attr('show', 'yes')
            $(el).parent().parent().find('.wa_hide2').show();
        } else {
            $(el).parents('.wa_row').addClass('greyed');
            $(el).parent().parent().attr('show', 'no')
            $(el).parent().parent().find('.wa_show').show();
        }

        wa_set_greyed_cells();
    }

    var wa_max_id = 0;
    wa_gen_id = function() {
        wa_max_id++;
        return 'g'+wa_max_id.toString() ;
    }

    wa_show_limit_info = function() {
        $("#dialog_info").html('<?php echo $this->get_string('limit') ?>');

        var buttons = {
            "<?php echo $this->get_string('close') ?>": function () {
                $(this).dialog("close");
            },
        }

        $("#dialog_info").dialog({ resizable: false, width: '650px', buttons: buttons });
    }

    var wa_edit_el = false;
    wa_edit_name = function(el) {
        wa_edit_el = el;
        $("#dialog_edit").find('input').val($(el).parent().parent().find('.name').text());

        var buttons = [
            {
                text: "<?php echo $this->get_string('save') ?>",
                class: "wa_blue_button",
                click: function() {
                    $(wa_edit_el).parent().parent().find('.name').html($("#dialog_edit").find('input').val());
                    $(this).dialog("close");
                }
            },
            {
                text: "<?php echo $this->get_string('cancel') ?>",
                click: function() {
                    $( this ).dialog( "close" );
                }
            },
        ];

        $("#dialog_edit").dialog({ resizable: false, width: '650px', buttons: buttons });
    }

    wa_edit_activity = function() {
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
            var row = $(plus).parents('.wa_row').find('.name').html();

            var i = $(plus).parents('.cell').index() + 1;
            var col = $('.wa_matrix .cols div:nth-child(' + i + ') .name').html();

            $('#editing_activity h2').html(row+', '+col);

            $('#editing_matrix').hide();
            $('#editing_activity').fadeIn();

            var content = '';
            if (wa_activities[location.hash]) {
                content = wa_activities[location.hash].content;
            }

            $('#id_content_editoreditable').html(content);
            $('#id_content_editor_ifr').contents().find('body').html(content);

            $('#activitiyform .essential > div').remove();
            $('#activitiyform .recommended > div').remove();
            $('#activitiyform .elective > div').remove();
            $('#activitiyform .added span').show();

            if (wa_activities[location.hash]) {
                if (wa_activities[location.hash].positions.essential.length) {
                    $('#activitiyform .added .essential span').hide();
                    $.each(wa_activities[location.hash].positions.essential, function () {
                        if (this.type == 'module') {
                            var a = $('.modules a[courseid="' + this.id + '"]');
                            wa_add_module(this.id, <?php echo WA_LEARNING_PATH_ESSENTIAL ?>, a.html(), this.methodology, this.date);
                        } else {
                            var a = $('.activity a[activityid="' + this.id + '"]');
                            wa_add_activity(this.id, <?php echo WA_LEARNING_PATH_ESSENTIAL ?>, a.html(), this.percent, this.date);
                        }
                    })
                }

                if (wa_activities[location.hash].positions.recommended.length) {
                    $('#activitiyform .added .recommended span').hide();
                    $.each(wa_activities[location.hash].positions.recommended, function () {
                        if (this.type == 'module') {
                            var a = $('.modules a[courseid="' + this.id + '"]');
                            wa_add_module(this.id, <?php echo WA_LEARNING_PATH_RECOMMENDED ?>, a.html(), this.methodology, this.date);
                        } else {
                            var a = $('.activity a[activityid="' + this.id + '"]');
                            wa_add_activity(this.id, <?php echo WA_LEARNING_PATH_RECOMMENDED ?>, a.html(), this.percent, this.date);
                        }
                    })
                }

                if (wa_activities[location.hash].positions.elective.length) {
                    $('#activitiyform .added .elective span').hide();
                    $.each(wa_activities[location.hash].positions.elective, function () {
                        if (this.type == 'module') {
                            var a = $('.modules a[courseid="' + this.id + '"]');
                            wa_add_module(this.id, <?php echo WA_LEARNING_PATH_ELECTIVE ?>, a.html(), this.methodology, this.date);
                        } else {
                            var a = $('.activity a[activityid="' + this.id + '"]');
                            wa_add_activity(this.id, <?php echo WA_LEARNING_PATH_ELECTIVE ?>, a.html(), this.percent, this.date);
                        }
                    })
                }
            }
        }
    }

    var wa_activities = { };
    wa_save_activity = function() {
        var essential = [];
        $('#activitiyform .essential > div').each(function() {
            var el = $(this).find('> div');
            if ($(el).hasClass('module')) {
                essential.push({ type: 'module', id: $(el).attr('courseid'), date: $(el).attr('date'), methodology: $(el).find('.methodology').html() });
            } else {
                essential.push({ type: 'activity', id: $(el).attr('activityid'), date: $(el).attr('date'), percent: $(el).attr('percent'), });
            }
        })

        var recommended = [];
        $('#activitiyform .recommended > div').each(function() {
            var el = $(this).find('> div');
            if ($(el).hasClass('module')) {
                recommended.push({ type: 'module', id: $(el).attr('courseid'), date: $(el).attr('date'), methodology: $(el).find('.methodology').html() });
            } else {
                recommended.push({ type: 'activity', id: $(el).attr('activityid'), date: $(el).attr('date'), percent: $(el).attr('percent'), });
            }
        })

        var elective = [];
        $('#activitiyform .elective > div').each(function() {
            var el = $(this).find('> div');
            if ($(el).hasClass('module')) {
                elective.push({ type: 'module', id: $(el).attr('courseid'), date: $(el).attr('date'), methodology: $(el).find('.methodology').html() });
            } else {
                elective.push({ type: 'activity', id: $(el).attr('activityid'), date: $(el).attr('date'), percent: $(el).attr('percent'), });
            }
        })


        var content = '';
        if ($('#id_content_editoreditable').length) {
            content = $('#id_content_editoreditable').html();
        }

        if ($('#id_content_editor_ifr').length) {
            content = $('#id_content_editor_ifr').contents().find('body').html();
        }

        wa_activities[location.hash] = {
            content: content,
            positions: {
                essential: essential,
                recommended: recommended,
                elective: elective
            }
        }

        wa_save();
        location.hash = '#save';

        /*wa_check_icons(location.hash);
        */
    }

    wa_add_position = function(pos, section) {
        if (section == <?php echo WA_LEARNING_PATH_ESSENTIAL ?>) {
            $('#activitiyform .added .essential span').hide();
            $('#activitiyform .added .essential').append(pos);
        } else
        if (section == <?php echo WA_LEARNING_PATH_RECOMMENDED ?>) {
            $('#activitiyform .added .recommended span').hide();
            $('#activitiyform .added .recommended').append(pos);
        } else {
            $('#activitiyform .added .elective span').hide();
            $('#activitiyform .added .elective').append(pos);
        }
    }

    wa_get_date_for_new = function() {
        if (wa_status == <?php echo WA_LEARNING_PATH_PUBLISH ?>) {
            var date = new Date();
            return Math.round(date.getTime() / 1000);
        } else {
            return 0;
        }
    }

    wa_add_module = function(id, section, name, methodology, isnew) {
        var date = isnew == 1 ? wa_get_date_for_new() : isnew;
        if ($('#activitiyform .added div[courseid="'+id+'"]').length > 0) {
            wa_already_added_message();
        } else {
            var pos = $('<div><div class="module" date="' + date + '" courseid="' + id + '"><div class="methodology">' + methodology + '</div><div class="name">' + name + '</div><div><button onclick="wa_remove_module(this); return false;"><?php echo $this->get_string('remove') ?></button></div></div></div>');
            $('.results a[courseid="'+id+'"]').addClass('added');
            wa_add_position(pos, section);
            return true;
        }
    }

    wa_add_activity = function(id, section, name, percent, isnew) {
        if ($('#activitiyform .added div[activityid="'+id+'"]').length > 0) {
            wa_already_added_message();
        } else {
            var date = isnew == 1 ? wa_get_date_for_new() : isnew;
            var pos = $('<div><div class="activity" date="' + date + '" activityid="' + id + '" percent="' + percent + '"><div class="title">' + name + ' (' + percent + ' %)' + '</div><div><button onclick="wa_remove_module(this); return false;"><?php echo $this->get_string('remove') ?></button></div></div></div>');
            $('.results a[activityid="'+id+'"]').addClass('added');
            wa_add_position(pos, section);
            return true;
        }
    }

    wa_remove_module = function(el) {
        delete_el = el;
        delete_type = 'activity';
        dialog_confirm.dialog("open");
    }

    wa_save = function() {
        var cols = [];
        $('.wa_matrix .col_header').each(function() {
            var col = { id: $(this).attr('id'), region: $(this).find('.select').val(), name: $(this).find('.name').html(), show: $(this).attr('show') == 'yes' }
            cols.push(col);
        })

        var rows = [];
        $('.wa_matrix .row_header').each(function() {
            var row = { id: $(this).parent().attr('id'), name: $(this).find('.name').html(), show: $(this).attr('show') == 'yes' }
            rows.push(row);
        })

        var data = { cols: cols, rows: rows, activities: wa_activities, max_id: wa_max_id, itemid: $('input[name="description_editor[itemid]"]').val(), };
        $('#matrixform input[name="matrix"]').val(JSON.stringify(data));
    }

    wa_check_icons = function(hash) {
        $.each(wa_activities, function(k) {
            if (!hash || hash == k) {
                if (this.positions.essential.length || this.positions.recommended.length || this.positions.elective.length) {
                    $('a[href="'+k+'"] img').attr('src', "<?php echo new \moodle_url('/local/wa_learning_path/img/plus3.png') ?>");
                } else {
                    if (this.content) {
                        $('a[href="'+k+'"] img').attr('src', "<?php echo new \moodle_url('/local/wa_learning_path/img/plus2.png') ?>");
                    } else {
                        $('a[href="'+k+'"] img').attr('src', "<?php echo new \moodle_url('/local/wa_learning_path/img/plus1.png') ?>");
                    }
                }
            }
        });
    }

    wa_already_added_message = function() {
        var buttons = [{
            text: "<?php echo $this->get_string('close') ?>",
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
    }

    wa_choose_element_to_add = function() {
        var buttons = [{
            text: "<?php echo $this->get_string('close') ?>",
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
    }

    dialog_confirm = $("#dialog-confirm").dialog({
        resizable: false,
        height: 200,
        width: 500,
        modal: true,
        autoOpen: false,
        buttons: {
            "<?php echo $this->get_string('confirm_delete') ?>": function () {
                wa_delete_confirmed(delete_el);
                $(this).dialog("close");
            },
            "<?php echo $this->get_string('no') ?>": function () {
                $(this).dialog("close");
            },
        }
    });

    $(document).ready(function() {
        $('#activitiyform .fstatic').html($('#wa_activity_form').html());

        wa_max_id = <?php echo (int)@$this->max_id ?>;
        wa_status = <?php echo (int)@$this->status ?>;

        <?php foreach($this->columns as $col): ?>
        wa_add_col(<?php echo json_encode($col) ?>);
        <?php endforeach; ?>

        <?php foreach($this->rows as $row): ?>
        wa_add_row(<?php echo json_encode($row) ?>);
        <?php endforeach; ?>

        <?php foreach($this->activities as $hash => $act): ?>
            wa_activities['<?php echo $hash ?>'] = <?php echo json_encode($act) ?>;
        <?php endforeach; ?>
        wa_isloading = true;

        <?php if (\wa_learning_path\lib\has_capability('editmatrixgrid')): ?>
        $("#editing_matrix .add_row").click(function() {
            wa_add_row({ show: 1, name: "<?php echo $this->get_string('new_row'); ?>"})
        })

        $("#editing_matrix .add_column").click(function() {
            wa_add_col({ show: 1, name: "<?php echo $this->get_string('new_column'); ?>"})
        })
        <?php endif; ?>

        wa_isloading = false;

        wa_fix_up_down_buttons();
        wa_fix_left_right_buttons();

        $('#id_submitbutton, #id_submitbutton2, #id_submitbutton3').click(function() {
            wa_save();
        })

        $('#id_acancel').attr('onclick', '');
        $('#id_acancel').click(function() {
            location.hash = '#cancel';
            $('#matrixform input[name="returnhash"]').val('');
            return false;
        })

        $('#id_asubmitbutton').click(function() {
            $('#matrixform input[name="returnhash"]').val(location.hash);
            wa_save_activity();
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
        })

        $('#activitiyform .modules .results a').click(function() {
            wa_to_add = $(this);
            $('#activitiyform .modules .results a').removeClass('selected');
            $(this).addClass('selected');
            $('#activitiyform .modules button').show();
            return false;
        })

        $('#activitiyform .activity .results a').click(function() {
            wa_to_add_activity = $(this);
            $('#activitiyform .activity .results a').removeClass('selected');
            $(this).addClass('selected');
            $('#activitiyform .activity button').show();
            return false;
        })

        $('#activitiyform .modules button.to_add').click(function() {
            if (wa_to_add) {
                if (wa_add_module($(wa_to_add).attr('courseid'), $(wa_to_add).parents('.tab').find('select').val(), $(wa_to_add).html(), $(wa_to_add).attr('methodology'), 1)) {
                    $('span.add_info').show();
                    setTimeout(function() { $('span.add_info').hide(); }, 3000);
                }
            } else {
                wa_choose_element_to_add();
            }

            return false;
        });

        $('#activitiyform .activity button.to_add').click(function() {
            if (wa_to_add_activity) {
                if (wa_add_activity($(wa_to_add_activity).attr('activityid'), $(wa_to_add_activity).parents('.tab').find('select').val(), $(wa_to_add_activity).html(), $('input[name="percent"]:checked').val(), 1)) {
                    $('span.add_info').show();
                    setTimeout(function() { $('span.add_info').hide(); }, 3000);
                }
            } else {
                wa_choose_element_to_add();
            }

            return false;
        });

        $('input[name="activitydraftid"]').val(<?php echo (int)$this->itemid ?>);

        wa_check_icons();

        window.onhashchange = function() {
            wa_edit_activity();
        }

        <?php if (isset($this->returnhash) && $this->returnhash): /* ?>
            location.hash = '<?php echo $this->returnhash ?>';
        <?php */ else: ?>
            location.hash = '#';
        <?php endif; ?>

        //wa_edit_activity();
    });
</script>
