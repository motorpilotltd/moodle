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
            <a type="button" href="<?php echo new \moodle_url($this->url, array('a' => 'print', 'id' => $this->id, 'ajax' => 1)) ?>" class="btn button-print btn-default "><?php echo $this->get_icon_html('icon_print') . $this->get_string('print_matrix') ?></a>
        <?php endif; ?>
        
        <?php if (\wa_learning_path\lib\has_capability('exportlearningmatrix')): ?>
            <a type="button" href="<?php echo new \moodle_url($this->url, array('a' => 'excel', 'id' => $this->id)) ?>" class="btn button-export btn-default"><?php echo $this->get_icon_html('icon_print') . $this->get_string('export_matrix') ?></a>
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
        ?>
        <div class="custom-select pull-left">
            <select name="levels" class="levels" data-url="<?php echo $this->base_url; ?>" data-placeholder="<?php echo get_string('all_levels', 'local_wa_learning_path') ?>"  >
                <option value="0"><?php echo get_string('all_levels', 'local_wa_learning_path') ?></option>
                <?php foreach($matrix->rows as $level): ?>
                    <?php if(!$level->show) continue; ?>
                    <option value="<?php echo $level->id ?>" <?php if (in_array($level->id, $this->levels)) echo 'selected="selected"'; ?>><?php echo $level->name ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="custom-select pull-left">
            <select name="region" class="region" data-url="<?php echo $this->base_url; ?>" data-placeholder="<?php echo get_string('all_regions', $this->pluginname) ?>">
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
                                <td class="lp_col_header" title="<?php echo $col->name ?>" >
                                    <?php
                                        $wrappedText = wordwrap(htmlentities(html_entity_decode(($col->name))), 25, '<br>');
                                        $wraps = explode('<br>', $wrappedText);
                                        $text = implode('<br>', array_splice($wraps,0,2));
                                    ?>
                                    <span class="matrix-text-left"><?php echo $text; ?></span>
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
                                        <a class="trigger-modal" href="<?php $this->cell_url->param('key', $key); echo $this->cell_url->out(true); ?>" data-toggle="modal" data-target="#myModal">
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
<?php
echo \html_writer::end_div();
?>

<script type='text/javascript'>
    $(document).ready(function(){
        
        var region = $('.region :selected').text();
        $('.region :selected').text('<?php echo get_string('region_text_part1', 'local_wa_learning_path'); ?>' + region + '<?php echo get_string('region_text_part2', 'local_wa_learning_path'); ?>');
        $('.learning_path_matrix .inner_scroll_top_content').css('width', parseInt($('.learning_path_matrix .lp_matrix').innerWidth()) + 'px');

        $(".learning_path_matrix .inner_scroll_top").scroll(function(){
            $(".learning_path_matrix .inner").scrollLeft($(".learning_path_matrix .inner_scroll_top").scrollLeft());
        });

        $(".learning_path_matrix .inner").scroll(function(){
            $(".learning_path_matrix .inner_scroll_top").scrollLeft($(".learning_path_matrix .inner").scrollLeft());
        });

        $(window).resize(function(){
            if($(this).width() < 1000) {
                $('.outer').parent().addClass('lp_matrix_scroll_enable');
            } else {
                if($('.outer').parent().hasClass('lp_matrix_scroll_enable')) {
                    $('.outer').parent().removeClass('lp_matrix_scroll_enable');
                }
            }
            
            var width = parseInt($('.learning_path_matrix').innerWidth()) - 200;
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
        
        $('select[name="levels"]').change(function() {
            var levels = '';
            var regions = '';

            if($('.learning_path_matrix .levels').val()) {
                levels = $('.learning_path_matrix .levels').val();
            }

            if($('.learning_path_matrix .region').val()) {
                regions = $('.learning_path_matrix .region').val();
            }

            var url = $(this).data('url') + '&levels=' + levels + '&regions=' + regions;
            window.location = url;
        });
        
        $('select[name="region"]').change(function() {
            var levels = '';
            var regions = '';

            if($('.learning_path_matrix .levels').val()) {
                levels = $('.learning_path_matrix .levels').val();
            }

            if($('.learning_path_matrix .region').val()) {
                regions = $('.learning_path_matrix .region').val();
            }

            var url = $(this).data('url') + '&levels=' + levels + '&regions=' + regions;
            window.location = url;
        });
    })
</script>
