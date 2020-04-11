<?php if ($this->id && \wa_learning_path\lib\has_capability('editlearningmatrix')): ?>
    <div class="wa_tabs">
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit&id='.$this->id)); ?>"><?php echo $this->get_string('tab_introduction') ?></a>
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit_matrix&id='.$this->id)); ?>" class="current"><?php echo $this->get_string('tab_matrix') ?></a>
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit_subscriptions&id='.$this->id)); ?>"><?php echo $this->get_string('tab_subscriptions') ?></a>
    </div>
<?php endif; ?>
<div class="lpath-edit-matrix">
    <div id="editing_matrix">
        <?php
            $this->display_error($this->get_flash_massage('success'), 'success');
            $this->display_error($this->get_flash_massage('error'), 'error');
            $this->display_error($this->get_flash_massage('other'), 'other');
            
            global $OUTPUT;
        ?>
        <div class="wrapper edit_matrix_view">

            <div class="buttons">
                <?php
                    $learningPath = \wa_learning_path\model\learningpath::get($this->id);
                    $createRoleUrl = new moodle_url('/local/wa_learning_path/index.php', ['c' => 'admin', 'a' => 'edit_role', 'id' => $this->id]);
                    if (!$learningPath->parent):
                        echo html_writer::link($createRoleUrl, $this->get_string('create_role_based_lp'), ['class' => 'create-role-button pull-right']);
                    else:
                        echo '<span class="buttons-label">'.get_string('select_role', 'local_wa_learning_path').'</span>';
                        if ($this->role === 0):
                            echo html_writer::link($createRoleUrl, '<div class="role-button pull-right">+</div>');
                        else:
                            $editRoleUrl = new moodle_url('/local/wa_learning_path/index.php', ['c' => 'admin', 'a' => 'edit_role', 'id' => $this->id, 'role' => $this->role]);
                            echo html_writer::link($editRoleUrl, '<div class="role-button pull-right"><i class="' . $this->get_icon_class('icon_edit') . '"></i></div>');
                        endif;
                        $selected = ($this->role !== 0) ? $this->role : 0;
                        echo html_writer::select($this->roles, 'roles', $selected, [0 => 'Default'], ['class' => 'roles pull-right', 'data-url' => $this->base_url]);
                    
                    endif;
                ?>
            </div>
            <div class="clearfix"></div>
            <div id="main">
                <div class="wa_matrix">
                    <div class="cols">
                        <div class="empty"></div>
                    </div>
                </div>
                
                <?php if (\wa_learning_path\lib\has_capability('editmatrixgrid')): ?>

                    <button class="add_row" style="width: 95%;margin: 20px 40px 0 0;" <?php if ($this->role !== 0) echo 'disabled="disabled"' ?>>
                        <?php echo $this->get_string('add_row') ?>
                    </button>
                
                <?php endif; ?>
            </div>
            <button class="add_column button-right" <?php if ($this->role !== 0) echo 'disabled="disabled"' ?>>
                <span class="button-text"><?php echo $this->get_string('add_column') ?></span>
            </button>
        </div>
        
        <?php if ($this->role === 0): ?>
            <div class="legend_container pull-right">
                <div class="legend">
                    <div class="legend_item">
                        <?php echo $this->get_icon_html('icon_ai_no_objectives'); ?>
                        <?php echo $this->get_string('no_objective_defined') ?>
                    </div>
                    <div class="legend_item">
                        <?php echo $this->get_icon_html('icon_ai_objectives'); ?>
                        <?php echo $this->get_string('objectives_defined') ?>
                    </div>
                    <div class="legend_item">
                        <?php echo $this->get_icon_html('icon_ai_modules_and_objectives'); ?>
                        <?php echo $this->get_string('both_defined') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="clearfix">
        </div>

        <div class="wa_hide">
            <div class="col_header">
                <div class="m3">
                    <?php
                        $regions = \wa_learning_path\lib\get_regions();
                        foreach($regions as $k => $region) {
                            $regionName =str_replace(' ', '', strtolower($region));
                            $class = 'class_' . $regionName;
                            $shortcut = 'shortcut_' . $regionName;
                            echo '<span id="'.$k.'"class="label pull-left hide '.$this->config->{$class}.'">'.$this->config->{$shortcut}.'</span>';
                        }
                    ?>
                    <div class="clearfix"></div>
                    <select class="select hide" id="regions" multiple="1">
                        <?php $regions = \wa_learning_path\lib\get_regions(); foreach($regions as $k => $region): ?>
                            <option value="<?php echo $k ?>" <?php if ($k == 0) echo "selected='1'"; ?>><?php echo $region ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                <span class="name">
                    <span class="text"></span>
                    <?php
                        if ($this->role === 0):
                            if (\wa_learning_path\lib\has_capability('editmatrixgrid')):
                
                                echo html_writer::link(new \moodle_url($this->url, array('a' => 'edit', 'id' => $this->id)),
                                    $this->get_icon_html('icon_edit'),
                                    array('title' => $this->get_string('edit'), 'class' => 'btn', 'data-toggle' => 'modal'));
                            endif;
                        endif;
                    ?>
                    <span class="tooltip"></span>
                </span>

                </div>
            </div>

            <div class="row_header">
            <span class="name">
                    <span class="text"></span>
                <?php
                    if ($this->role === 0):
                        if (\wa_learning_path\lib\has_capability('editmatrixgrid')):
                
                            echo html_writer::link(new \moodle_url($this->url, array('a' => 'edit', 'id' => $this->id)),
                                $this->get_icon_html('icon_edit'),
                                array('title' => $this->get_string('edit'), 'class' => 'btn edit', 'data-toggle' => 'modal'));
                        endif;
                    endif; ?>
                <span class="tooltip"></span>
            </span>

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
    
    <?php if ($this->role !== 0): ?>
        <div id="editing_role_activity" class="wa_hide">
            <h2></h2>
            <?php // $this->editactivityform->display(); ?>
        </div>
    <?php endif; ?>

    <div class="modal fade" id="myModalColumn" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <a href="#" type="button" class="btn btn-default pull-right" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></a>
                    <h1 class="modal-title" id="myModalLabel"><?php echo get_string('columnModalLabel', 'local_wa_learning_path'); ?></h1>
                </div>
                <div class="modal-body">
                    <div class="form-inline">
                        <div class="form-group col-xs-9">
                            <input type="text" name="name" id="name" class="form-control">
                        </div>
                        <div class="form-group col-xs-3">
                            <label for="position" class="col-xs-8 control-label positionLabel"><?php echo get_string('columnPosition', 'local_wa_learning_path'); ?></label>
                            <input type="text" name="position" class=" col-xs-4" id="position" placeholder="">
                        </div>
                    </div>
                    <div class="form-inline">
                        <div class="form-group col-xs-12">
                            <div class="textarea">
                                <textarea id="description" name="description form-control" class="col-xs-12" placeholder="<?php echo get_string('description', 'local_wa_learning_path'); ?>"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-inline visibleRow">
                        <div class="form-group col-xs-6">
                            <div class="checkbox">
                                <label for="inputPassword3" class="col-sm-12 control-label">
                                    <input type="checkbox" id="visible"> <?php echo get_string('visible', 'local_wa_learning_path'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="inputPassword3" class="col-sm-12 select-regions control-label">
                                <select class="select" id="regions" multiple="1">
                                    <?php $regions = \wa_learning_path\lib\get_regions(); foreach($regions as $k => $region): ?>
                                        <option value="<?php echo $k ?>" <?php if ($k == 0) echo "selected='1'"; ?>><?php echo $region ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="submit" id="cancel" class="btn btn-default pull-right"><?php echo get_string('cancel', 'local_wa_learning_path'); ?></button>
                        <button type="submit" id="save" data-id="" class="btn btn-primary pull-right"><?php echo get_string('save', 'local_wa_learning_path'); ?></button>
                        <button type="submit" id="delete" data-id="" data-dismiss="modal" aria-label="Close" class="btn pull-right"><?php echo get_string('deleteColumn', 'local_wa_learning_path'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModalRow" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <a href="#" type="button" class="btn btn-default pull-right" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></a>
                    <h1 class="modal-title" id="myModalLabel"><?php echo get_string('rowModalLabel', 'local_wa_learning_path'); ?></h1>
                </div>
                <div class="modal-body">
                    <div class="form-inline">
                        <div class="form-group col-xs-10">
                            <input type="text" name="name" id="name" class="form-control">
                        </div>
                        <div class="form-group col-xs-2">
                            <label for="position" class="col-xs-8 control-label positionLabel"><?php echo get_string('rowPosition', 'local_wa_learning_path'); ?></label>
                            <input type="text" name="position" class="col-xs-4" id="position" placeholder="">
                        </div>
                    </div>
                    <div class="form-inline visibleRow">
                        <div class="form-group">
                            <div class="checkbox">
                                <label for="inputPassword3" class="col-sm-12 control-label">
                                    <input type="checkbox" id="visible"> <?php echo get_string('visible', 'local_wa_learning_path'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" id="cancel" class="btn btn-default pull-right"><?php echo get_string('cancel', 'local_wa_learning_path'); ?></button>
                        <button type="submit" id="save" data-id="" class="btn btn-primary pull-right"><?php echo get_string('save', 'local_wa_learning_path'); ?></button>
                        <button type="submit" id="delete" data-id="" data-dismiss="modal" aria-label="Close" class="btn pull-right"><?php echo get_string('deleteRow', 'local_wa_learning_path'); ?></button>
                    </div>
                </div>
            </div>
        </div>
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
                <button type="button" class="wa_blue_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button">
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
                <button class="add_activity">
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
</div>
