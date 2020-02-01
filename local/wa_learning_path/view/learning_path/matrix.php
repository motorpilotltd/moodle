<?php
global $USER, $OUTPUT, $CFG;
echo \html_writer::start_div('learning_path_matrix');
echo $OUTPUT->heading($this->learning_path->title);

$this->display_error($this->get_flash_massage('success'), 'success');
$this->display_error($this->get_flash_massage('error'), 'error');
$this->display_error($this->get_flash_massage('other'), 'other');

$no_objective_defined = \html_writer::div('', 'empty_box');
$objectives_defined = \html_writer::img(new \moodle_url('/local/wa_learning_path/pix/objectives_defined.svg'), 'objectives_defined');
$modules_and_activities_defined = \html_writer::img(new \moodle_url('/local/wa_learning_path/pix/modules_and_activities_defined.svg'), 'modules_and_activities_defined');
$in_progress = \html_writer::img(new \moodle_url('/local/wa_learning_path/pix/in_progress.svg'), 'in_progress');
$cell_url = new \moodle_url($this->url, array())

?>
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
    <?php if(empty($this->preview)): ?>
        <div class="btn-group pull-right" role="group" >
            <?php if (\wa_learning_path\lib\has_capability('printlearningmatrix')): ?>
                <a type="button" href="<?php echo new \moodle_url($this->url, array('a' => 'print', 'id' => $this->id, 'ajax' => 1)) ?>" class="btn btn-default "><?php echo $this->get_string('print_matrix') ?></a>
            <?php endif; ?>

            <?php if (\wa_learning_path\lib\has_capability('exportlearningmatrix')): ?>
                <a type="button" href="<?php echo new \moodle_url($this->url, array('a' => 'excel', 'id' => $this->id)) ?>" class="btn btn-default"><?php echo $this->get_string('export_matrix') ?></a>
            <?php endif; ?>

            <?php if (\wa_learning_path\lib\has_capability('amendlearningcontent')): ?>
                <a type="button" href="<?php echo new \moodle_url($this->url, array('c' => 'admin', 'a' => 'edit_matrix', 'id' => $this->id)) ?>" class="btn btn-default"><?php echo $this->get_string('edit_learning_matrix') ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="clearfix"></div>
    <div class="<?php if($this->matrix->visible_cols > 12): ?>lp_matrix_scroll_enable<?php endif; ?>">
        <div class="outer">
            <div class="inner_scroll_top">
                <div class="inner_scroll_top_content">&nbsp;</div>
            </div>
            <div class="inner">
                <table class="lp_matrix">
                    <tbody>
                        <tr>
                            <th class="lp_empty">
                                <div class="legend">
                                    <div class="key pull-left"><?php echo $this->get_string('key'); ?></div>
                                    <div class="clearfix"></div>
                                    <div class="legend_toggle">
                                        <div class="legend_item"><?php echo $no_objective_defined ?> <?php echo $this->get_string('no_objective_defined') ?></div>
                                        <div class="legend_item"><?php echo $objectives_defined ?> <?php echo $this->get_string('objectives_defined') ?></div>
                                        <div class="legend_item"><?php echo $modules_and_activities_defined ?> <?php echo $this->get_string('modules_and_activities_defined') ?></div>
                                        <div class="legend_item"><?php echo $in_progress ?> <?php echo $this->get_string('learning_in_progress') ?></div>
                                    </div>
                                </div>
                            </th>
                            <?php foreach ($this->matrix->cols as $col): ?>
                                <?php if (!$col->show || !wa_learning_path\model\learningpath::check_regions_match($this->regions, $col->region)) continue; ?>
                                <td class="lp_col_header" title="<?php echo $col->name ?>" >
                                    <svg version="1.1" width="18" height="175">
                                    <text x="-165" y="14" text-anchor="start" transform="rotate(270)"><?php echo htmlentities(substr(html_entity_decode($col->name), 0, 20)); ?></text>
                                    </svg>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php foreach ($this->matrix->rows as $k => $row): ?>
                            <?php if (!$row->show || (!empty($this->levels) && !in_array($row->id, $this->levels) ) ) continue; ?>
                            <tr class="">
                                <th class="lp_row_header" title="<?php echo $this->matrix->rows[$k]->name ?>" >
                                    <?php if (!$this->matrix->rows[$k]->show) continue; ?>
                                    <div class="cell_container">
                                        <?php echo $this->matrix->rows[$k]->name ?>
                                    </div>
                                </th>
                                <?php foreach ($this->matrix->cols as $k => $col): $key = "#{$col->id}_{$row->id}"; ?>
                                    <?php if (!$col->show || !wa_learning_path\model\learningpath::check_regions_match($this->regions, $col->region)) continue; ?>
                                    <td class="lp_cell clickable <?php if(!empty($this->key) && $this->key == $key) echo "active"; ?>" id="<?php echo str_replace('#', '', $key) ?>" >
                                        <div class="cell_icon_container">
                                        <a href="<?php $this->cell_url->param('key', $key); echo $this->cell_url->out(true); ?>">
                                        <?php
                                        if(isset($this->matrix->activities->{$key})) {
                                            $status = \wa_learning_path\model\learningpath::get_cell_info($this->activities->{$key}, $this->regions, $this->learning_path->subscribed);
                                            $object = $this->matrix->activities->{$key};
                                            if(empty($object)) {
                                                echo $no_objective_defined;
                                            } else if($status->in_progress) {
                                                echo $in_progress;
                                            } else if($status->modules_count > 0 || $status->activities_count > 0) {
                                                echo $modules_and_activities_defined;
                                            } else if($status->objectives_defined) {
                                                echo $objectives_defined;
                                            } else{
                                                echo $no_objective_defined;
                                            }
                                        } else {
                                            echo $no_objective_defined;
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
            </div>
        </div>
    </div>
    <?php if($this->cell): ?>
        <?php require_once("$CFG->dirroot/local/wa_learning_path/view/learning_path/matrix_activities.php"); ?>
    <?php endif; ?>
<?php endif; ?>
<?php
echo \html_writer::end_div();
?>

<script type='text/javascript'>
    $(document).ready(function(){
        $('.learning_path_matrix .inner_scroll_top_content').css('width', parseInt($('.learning_path_matrix .lp_matrix').innerWidth()) + 'px');

        $(".learning_path_matrix .inner_scroll_top").scroll(function(){
            $(".learning_path_matrix .inner").scrollLeft($(".learning_path_matrix .inner_scroll_top").scrollLeft());
        });

        $(".learning_path_matrix .inner").scroll(function(){
            $(".learning_path_matrix .inner_scroll_top").scrollLeft($(".learning_path_matrix .inner").scrollLeft());
        });

        $(window).resize(function(){
            var width = parseInt($('.learning_path_matrix').innerWidth()) - 180;
            $('.learning_path_matrix .lp_matrix_scroll_enable .inner, .learning_path_matrix .inner_scroll_top').css('width', width + 'px');
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
    })
</script>
