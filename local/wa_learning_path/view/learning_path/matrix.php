<?php
global $USER, $OUTPUT, $CFG;


$this->display_error($this->get_flash_massage('success'), 'success');
$this->display_error($this->get_flash_massage('error'), 'error');
$this->display_error($this->get_flash_massage('other'), 'other');

$objectives_defined = $this->get_icon_html('icon_ui_objectives', 'fa-lpath-cell');
$modules_and_activities_defined = $this->get_icon_html('icon_ui_modules_and_objectives', 'fa-lpath-cell');
$in_progress = $this->get_icon_html('icon_ui_in_progress', 'fa-lpath-cell');
$completed = $this->get_icon_html('icon_ui_complete', 'fa-lpath-cell');
$cell_url = new \moodle_url($this->url, array());
$preview = optional_param('preview', 0, PARAM_INT);

?>
<?php
    echo \html_writer::start_div('learning_path_matrix');
?>
<span class="lpath-header pull-left"><?php echo $this->learning_path->title; ?>
    <div class="btn-group" role="group">
        <?php
            echo $this->subscribeButton;
        ?>
    </div></span>
<?php if(empty($this->preview) && !empty($this->matrix)): ?>

    <div class="btn-group pull-right" role="group" >
        <?php if (\wa_learning_path\lib\has_capability('printlearningmatrix')): ?>
            <?php if ($this->role): $params = ['a' => 'print', 'id' => $this->id, 'ajax' => 1, 'role' => $this->role->id]; ?>
            <?php else: $params = ['a' => 'print', 'id' => $this->id, 'ajax' => 1]; ?>
            <?php endif; ?>
            <?php if ($this->regions) $params['regions'] = implode(',', $this->regions); ?>
            <a type="button" href="<?php echo new \moodle_url($this->url, $params) ?>" class="btn button-print btn-default "><?php echo $this->get_icon_html('icon_print') . $this->get_string('print_matrix') ?></a>
        <?php endif; ?>
        
        <?php if (\wa_learning_path\lib\has_capability('exportlearningmatrix')): ?>
            <?php if ($this->role): $params = ['a' => 'excel', 'id' => $this->id, 'role' => $this->role->id]; ?>
            <?php else: $params = ['a' => 'excel', 'id' => $this->id]; ?>
            <?php endif; ?>
            <?php if ($this->regions) $params['regions'] = implode(',', $this->regions); ?>
            <a type="button" href="<?php echo new \moodle_url($this->url, $params) ?>" class="btn button-export btn-default"><?php echo $this->get_icon_html('icon_print') . $this->get_string('export_matrix') ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="clearfix"></div>
<?php if($this->preview): ?>
    <?php echo $this->display_error($this->get_string('you_are_in_preview_mode'), 'info'); ?>
<?php elseif(\wa_learning_path\lib\has_capability('viewlearningpath') && $this->learning_path->status != WA_LEARNING_PATH_PUBLISH): ?>
    <?php if($this->learning_path->status == WA_LEARNING_PATH_DELETED): ?>
        <?php echo $this->display_error($this->get_string('view_of_deleted_lerning_path'), 'error'); ?>
    <?php elseif($this->learning_path->status == WA_LEARNING_PATH_DRAFT): ?>
        <?php echo $this->display_error($this->get_string('view_of_draft_version'), 'info'); ?>
    <?php else: ?>
        <?php echo $this->display_error($this->get_string('view_of_publish_not_visible_version'), 'info'); ?>
    <?php endif; ?>
<?php endif; ?>

<?php if(empty($this->matrix)): ?>
    <?php echo $this->display_error($this->get_string('matrix_is_empty'), 'info'); ?>
<?php elseif (empty($this->matrix->visible_cols)): ?>
    <?php echo html_writer::tag('p', $this->get_string('matrix_for_region_is_empty', implode(' or ', $this->regionnames))); ?>
    <?php if (!empty($this->regionhascontent)) : ?>
        <?php echo $this->get_string('matrix_regions_have_path'); ?>
        <ul>
            <?php foreach ($this->regionhascontent as $regionid => $regionname) : ?>
            <li><?php echo html_writer::link(new \moodle_url($this->url, ['a' => 'matrix', 'id' => $this->id, 'regions' => $regionid]), $regionname); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php else: ?>
    <div class="clearfix"></div>
    <div class="filter-navbar">
        <?php
            $matrix = json_decode($this->learning_path->matrix);
            $allregions = \wa_learning_path\lib\get_regions();

            $noRoleVisible = true;
            if (isset($this->roles)) {
                foreach ($this->roles as $role) {
                    if ($role->visible) {
                        $noRoleVisible = false;
                        break;
                    }
                }
            }
        ?>
       
            <?php if (!$this->roles || $noRoleVisible): ?>
              <div class="pull-left">
                <select name="levels" class="levels-multiselect" data-url="<?php echo $this->base_url; ?>" data-placeholder="<?php echo get_string('all_levels', 'local_wa_learning_path') ?>" multiple="1" >
                    <?php foreach($matrix->rows as $level): ?>
                        <?php if(!$level->show) continue; ?>
                        <option value="<?php echo $level->id ?>" <?php if (in_array($level->id, $this->levels)) echo 'selected="selected"'; ?>><?php echo $level->name ?></option>
                    <?php endforeach; ?>
                </select>
              </div>
            <?php else: ?>
            <div class="custom-select pull-left">
                <select name="roles" class="levels" data-url="<?php echo $this->base_url; ?>" data-placeholder="<?php echo get_string('all_roles', 'local_wa_learning_path') ?>"  >
                    <option value="0"><?php echo get_string('all_roles', 'local_wa_learning_path') ?></option>
                    <?php foreach($this->roles as $role): ?>
                        <?php if(!$role->visible) continue; ?>
                        <option value="<?php echo $role->id ?>" <?php if ($this->role && $role->id == $this->role->id) echo 'selected="selected"'; ?>><?php echo $role->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

        <div class="custom-select pull-left">
            <select name="region" class="region" data-url="<?php echo $this->base_url; ?>" data-placeholder="<?php echo get_string('all_regions', $this->pluginname) ?>">
                <option value="-1"><?php echo get_string('all_regions', 'local_wa_learning_path') ?></option>
                <?php foreach($allregions as $id => $region): ?>
                    <option value="<?php echo $id ?>" <?php if( is_array($this->selectedregions) && in_array($id, $this->selectedregions )) echo 'selected="selected"'; ?>><?php echo $region ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="clearfix">
        </div>
    </div>
    <div class="<?php if($this->matrix->visible_cols > 15): ?>lp_matrix_scroll_enable<?php endif; ?>">
        <div class="outer">
            <div class="inner_scroll_top">
                <div class="inner_scroll_top_content">&nbsp;</div>
            </div>
            <div class="inner">
                <table class="lp_matrix">
                    <tbody>
                        <tr>
                            <th class="lp_empty">

                            </th>
                            <?php foreach ($this->matrix->cols as $col): ?>
                                <?php if (!$col->show || !wa_learning_path\model\learningpath::check_regions_match($this->regions, $col->region)) continue; ?>
                                <td class="lp_col_header" data-column="<?php echo $col->id; ?>" title="<?php echo $col->name ?>" >
                                    <?php
                                        $wrappedText = wordwrap(htmlentities(html_entity_decode(($col->name))), 25, '<br>');
                                        $wraps = explode('<br>', $wrappedText);
                                        $text = implode('<br>', array_splice($wraps,0,2));
                                    ?>
                                    <span class="matrix-text-left"><?php echo $text; ?>
                                        <?php if (isset($col->description) && $col->description): ?>
                                            <a class="btn-link help" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;><p><?php echo $col->description; ?></p>
                                            </div> " data-html="true" tabindex="0" data-trigger="focus">
                                                <img class="icon " alt="<?php echo $this->get_string('description'); ?>" title="" src="<?php echo $OUTPUT->image_url('help', 'moodle')->out(false); ?>">
                                            </a>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach ($this->matrix->rows as $k => $row): ?>
                            <?php if (!$row->show || (!empty($this->levels) && !in_array($row->id, $this->levels) ) ) continue; ?>
                            <tr class="">
                                <th class="lp_row_header" data-row="<?php echo $row->id; ?>" title="<?php echo $this->matrix->rows[$k]->name ?>" >
                                    <?php if (!$this->matrix->rows[$k]->show) continue; ?>
                                    <div class="cell_container">
                                        <span class="row_name" data-name="<?php echo $this->matrix->rows[$k]->name ?>"><?php echo $this->matrix->rows[$k]->name ?></span>
                                    </div>
                                </th>
                                <?php foreach ($this->matrix->cols as $k => $col): $key = "#{$col->id}_{$row->id}"; ?>
                                    <?php if (!$col->show || !wa_learning_path\model\learningpath::check_regions_match($this->regions, $col->region)) continue; ?>
                                    <td class="lp_cell clickable <?php if(!empty($this->key) && $this->key == $key) echo "active"; ?> <?php if (in_array($key, $this->enabledActivities)) echo 'cell-selected'; ?>" id="<?php echo str_replace('#', '', $key) ?>" >
                                        <div class="cell_icon_container">
                                        <a class="trigger-modal" href="<?php $this->cell_url->param('key', $key); if (isset($this->role->id)) $this->cell_url->param('role', $this->role->id); echo $this->cell_url->out(true); ?>" data-toggle="modal" data-target="#myModal">
                                        <?php
                                        if(isset($this->matrix->activities->{$key})) {
                                            $status = \wa_learning_path\model\learningpath::get_cell_info($this->activities->{$key}, $this->regions, $this->learning_path->subscribed);
                                            $object = $this->matrix->activities->{$key};
                                            
                                            if(empty($object)) {
                                                echo $no_objective_defined;
                                            }else if($status->completed) {
                                                echo $completed;
                                            } else if($status->in_progress) {
                                                echo $in_progress;
                                            } else if($status->modules_count > 0 || $status->activities_count > 0) {
                                                echo $modules_and_activities_defined;
                                            } else if($status->objectives_defined) {
                                                echo $objectives_defined;
                                            } else{
                                            }
                                        } else {
                                        }
                                        ?>
                                        </a>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                    <div class="legend">
                        <div class="legend_item"><?php echo $completed ?> <?php echo $this->get_string('completed') ?></div>
                        <div class="legend_item"><?php echo $in_progress ?> <?php echo $this->get_string('learning_in_progress') ?></div>
                        <div class="legend_item"><?php echo $modules_and_activities_defined ?> <?php echo $this->get_string('modules_and_activities_defined') ?></div>
                        <div class="legend_item"><?php echo $objectives_defined ?> <?php echo $this->get_string('objectives_defined') ?></div>
                    </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <a href="#" type="button" class="btn btn-default pull-right" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></a>
                <a href="#" id="print_placeholder"></a>
                <h1 class="modal-title" id="myModalLabel"><?php echo $this->learning_path->title; ?></h1>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="completionModal" tabindex="-1" role="dialog" aria-labelledby="completionModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <a href="#" type="button" class="btn btn-default pull-right" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></a>
                <h1 class="modal-title" id="completionModalLabel"><?php echo $this->get_string('guide_title'); ?></h1>
                <h5 id="completionModalLabel2"></h5>
                <h5 id="completionModalLabel3"></h5>
            </div>
            <div class="modal-body">
                <div class="form-inline">
                    <div class="form-group col-xs-12">
                        <label for="notes" class="col-xs-12 control-label label-notes"><?php echo $this->get_string('guide_notes'); ?></label>
                        <textarea name="notes" class="col-xs-12" id="notes" placeholder=""></textarea>
                    </div>
                </div>
                <div class="form-inline">
                    <div class="form-group col-xs-12">
                        <label for="date" class="col-xs-12 control-label label-date"><?php echo $this->get_string('guide_date'); ?></label>
                        <input type="date" name="date" id="date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="form-inline">
                    <div class="form-group col-xs-12">
                        <div class="checkbox">
                            <label for="confirmation" class="col-sm-12 control-label label-confirmation">
                                <input type="checkbox" id="confirmation"> <?php echo $this->get_string('guide_confirm'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="buttons">
                    <button type="submit" id="save2" data-id="" class="btn btn-primary pull-left"><?php echo get_string('save', 'local_wa_learning_path'); ?></button>
                    <button type="submit" id="cancel2" class="btn btn-default pull-left"><?php echo get_string('cancel', 'local_wa_learning_path'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo \html_writer::end_div();
?>

<script type='text/javascript'>
    $(document).ready(function(){
        var region = $('.region :selected').text();
        $('.region :selected').text('<?php echo get_string('region_text_part1', 'local_wa_learning_path'); ?>' + region + '<?php if (!in_array(-1, $this->regions)): echo get_string('region_text_part2', 'local_wa_learning_path'); endif; ?>');
        $('.learning_path_matrix .inner_scroll_top_content').css('width', parseInt($('.learning_path_matrix .lp_matrix').innerWidth()) + 'px');

        $(".learning_path_matrix .inner_scroll_top").scroll(function(){
            $(".learning_path_matrix .inner").scrollLeft($(".learning_path_matrix .inner_scroll_top").scrollLeft());
        });

        $(".learning_path_matrix .inner").scroll(function(){
            $(".learning_path_matrix .inner_scroll_top").scrollLeft($(".learning_path_matrix .inner").scrollLeft());
        });

        $(window).resize(function(){
                var width = parseInt($(window).width() - 30);
                var row_name = $('.cell_container .row_name');
                var cells = $('.learning_path_matrix .lp_col_header');
                var sumCellsWidth = 0;

                cells.each(function () {
                    sumCellsWidth += 59;
                });
                
                if ($(this).width() <= 600) {
                    var rowWidth = 200;

                    if(sumCellsWidth >= width - rowWidth) {
                        if (!$('.outer').parent().hasClass('lp_matrix_scroll_enable')) {
                            $('.outer').parent().addClass('lp_matrix_scroll_enable');
                        }
                        
                        $('.learning_path_matrix .inner').css('width', width - rowWidth + 'px');
                        $('.learning_path_matrix .inner_scroll_top').css('width', width - rowWidth + 'px');
                        $('.learning_path_matrix .lp_matrix_scroll_enable .inner ').css('margin-left', '200px');
                        $('.learning_path_matrix .inner_scroll_top').css('margin-left', '200px');
                    } else {
                        if($('.outer').parent().hasClass('lp_matrix_scroll_enable')) {
                            $('.outer').parent().removeClass('lp_matrix_scroll_enable');
                        }
                        $('.learning_path_matrix .inner').css('width', '');
                        $('.learning_path_matrix .inner').css('margin-left', '');
                        $('.learning_path_matrix .inner_scroll_top').css('width', (width - 200) + 'px');
                        $('.learning_path_matrix .inner_scroll_top').css('margin-left', '200px');
                    }


                    $('.learning_path_matrix .lp_row_header  .cell_container').css('width', rowWidth + 'px');
                    $('.learning_path_matrix .lp_row_header').css('width', rowWidth + 'px');


                    row_name.each(function () {
                        $(this).text($(this).attr('data-name').replace(/^(.{18}[.]*).*/, "$1 ..."));
                    });
                } else if ($(this).width() > 600 && $(this).width() < 1000) {
                    var rowWidth = 250;
                    
                    if(sumCellsWidth > width - rowWidth) {
                        if (!$('.outer').parent().hasClass('lp_matrix_scroll_enable')) {
                            $('.outer').parent().addClass('lp_matrix_scroll_enable');
                        }
                        $('.learning_path_matrix .inner').css('width', width - rowWidth+ 'px');
                        $('.learning_path_matrix .inner_scroll_top').css('width', (width - rowWidth) + 'px');
                        $('.learning_path_matrix .lp_matrix_scroll_enable .inner ').css('margin-left', '250px');
                        $('.learning_path_matrix .inner_scroll_top').css('margin-left', '250px');
                    } else {
                        if($('.outer').parent().hasClass('lp_matrix_scroll_enable')) {
                            $('.outer').parent().removeClass('lp_matrix_scroll_enable');
                        }
                        $('.learning_path_matrix .inner').css('width', '');
                        $('.learning_path_matrix .inner').css('margin-left', '');
                        $('.learning_path_matrix .inner_scroll_top').css('width', (width - 200) + 'px');
                        $('.learning_path_matrix .inner_scroll_top').css('margin-left', '200px');
                    }


                    $('.learning_path_matrix .lp_row_header  .cell_container').css('width', rowWidth + 'px');
                    $('.learning_path_matrix .lp_row_header').css('width', rowWidth + 'px');

                    row_name.each(function () {
                        $(this).text($(this).attr('data-name').replace(/^(.{24}[.]*).*/, "$1 ..."));
                    });
                } else {
                    if($('.outer').parent().hasClass('lp_matrix_scroll_enable')) {
                        $('.outer').parent().removeClass('lp_matrix_scroll_enable');
                    }
                    
                    $('.learning_path_matrix .lp_row_header .cell_container').css('width', '');
                    $('.learning_path_matrix .lp_row_header').css('width', '');
                    $('.learning_path_matrix .inner').css('width', '100%');
                    $('.learning_path_matrix .inner').css('margin-left', '');
                    $('.learning_path_matrix .inner_scroll_top').css('width', (width - 200) + 'px');
                    $('.learning_path_matrix .inner_scroll_top').css('margin-left', '200px');


                    row_name.each(function () {
                        $(this).text($(this).attr('data-name').replace(/^(.{60}[.]*).*/, "$1 ..."));
                    });
                }
                
                $('.learning_path_matrix .inner_scroll_top_content').css('width', parseInt($('.learning_path_matrix .lp_matrix').innerWidth()) + 'px');
                
        }).resize();

        $( '.lp_matrix .lp_col_header' ).tooltip({
            position: {
                my: "center right",
                at: "right-10 top+60"
            }
        });
        $( '.lp_matrix .lp_row_header' ).tooltip({
            position: {
                my: "right center"
            }
        });

        $('select[name="levels"]').on('select2:select', function (e) {
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

            var url = $(this).data('url') + '&levels=' + levels + '&regions=' + regions + '&role=' + roles;
            window.location = url;
        });
        
        // $('select[name="levels"]').change(function() {
        //     var levels = '';
        //     var regions = '';
        //     var roles = '';
        //
        //     if($('select[name="levels"]').val()) {
        //         levels = $('select[name="levels"]').val();
        //     }
        //
        //     if($('select[name="roles"]').val()) {
        //         roles = $('select[name="roles"]').val();
        //     }
        //
        //     if($('.learning_path_matrix .region').val()) {
        //         regions = $('.learning_path_matrix .region').val();
        //     }
        //
        //     var url = $(this).data('url') + '&levels=' + levels + '&regions=' + regions + '&role=' + roles;
        //     window.location = url;
        // });
        
        $('select[name="region"]').change(function() {
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

            var url = $(this).data('url') + '&levels=' + levels + '&regions=' + regions + '&role=' + roles;
            window.location = url;
        });

        $('select[name="roles"]').change(function() {
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

            var url = $(this).data('url') + '&levels=' + levels + '&regions=' + regions + '&role=' + roles;
            window.location = url;
        });
    })
</script>
