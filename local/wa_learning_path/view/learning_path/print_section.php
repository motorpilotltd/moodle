<?php
    global $CFG;
    $no_objective_defined = \html_writer::div('', 'empty_box');
    $objectives_defined = \html_writer::img(new \moodle_url('/local/wa_learning_path/pix/objectives_defined.svg'), 'objectives_defined', array('style' => 'width: 30px; height: 30px;'));
    $modules_and_activities_defined = \html_writer::img(new \moodle_url('/local/wa_learning_path/pix/modules_and_activities_defined.svg'), 'modules_and_activities_defined', array('style' => 'width: 30px; height: 30px;'));
    $in_progress = \html_writer::img(new \moodle_url('/local/wa_learning_path/pix/in_progress.svg'), 'in_progress', array('style' => 'width: 30px; height: 30px;'));
    $cell_url = new \moodle_url($this->url, array())
?>
<h2><?php echo $this->learning_path->title ?></h2>
<table id="matrix" style="">
    <thead>
        <th>
        </th>
        <?php
        for ($c = 0; $c < count($this->matrix->cols); $c++) {
            if ($this->matrix->cols[$c]->show && wa_learning_path\model\learningpath::check_regions_match($this->regions, $this->matrix->cols[$c]->region)) {
                echo "<th>" . $this->matrix->cols[$c]->name . '</th>';
            }
        }
        ?>
    </thead>
    <tbody>
    <?php for ($r = 0; $r < count($this->matrix->rows); $r++): if ($this->matrix->rows[$r]->show) { ?>
        <tr>
            <td>
                <?php echo $this->matrix->rows[$r]->name ?>
            </td>
            <?php for ($c = 0; $c < count($this->matrix->cols); $c++): if ($this->matrix->cols[$c]->show) { ?>
                <?php
                    $id = '#' . $this->matrix->cols[$c]->id . '_' . $this->matrix->rows[$r]->id;
                ?>
                <?php if (!wa_learning_path\model\learningpath::check_regions_match($this->regions, $this->matrix->cols[$c]->region)) continue; ?>
                <td <?php if ($id == $this->key) echo "class='highlight'" ?>>
                    <?php
                    if(isset($this->matrix->activities->{$id})) {
                        $status = \wa_learning_path\model\learningpath::get_cell_info($this->activities->{$id}, $this->regions, $this->learning_path->subscribed);
                        $object = $this->matrix->activities->{$id};
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

                        if ($id == $this->key) {
                            $title = $this->matrix->rows[$r]->name.', '.$this->matrix->cols[$c]->name;
                            $selected = $object;
                        }
                    } else {
                        echo $no_objective_defined;
                    }
                    ?>
                </td>
            <?php } endfor; ?>
        </tr>
    <?php } endfor; ?>
    </tbody>
</table>

<?php if (isset($selected)): ?>
    <h2><?php echo $title; ?></h2>
    <?php
        echo $this->cell->content;
    ?>
<?php endif; ?>

<div class="matrix_activities padd_5">
    <?php if(!empty($this->position)): $first = true; ?>
        <div class="tab_content>
            <div class="no-overflow">
                <?php if($this->activities && count($this->cell->positions->{$this->position})): ?>
                    <table class="list_of_activities">
                        <tbody>
                        <?php foreach($this->cell->positions->{$this->position} as $activity): ?>
                            <?php
                            if(empty($activity->id)){
                                continue;
                            }

                            if (!wa_learning_path\model\learningpath::check_regions_match($this->regions, $activity->region)) {
                                continue;
                            }

                            $id = $activity->type . "_" . $activity->id;
                            $icon = '';
                            if($activity->type == 'module'){
                                $title = $activity->fullname;
                                $coursecontext = context_course::instance($activity->id);
                                $options = array();
                                $summary = \file_rewrite_pluginfile_urls($activity->description, 'pluginfile.php', $coursecontext->id, 'course', 'summary', null);
                                $description = format_text($summary, $activity->summaryformat, $options, $activity->id);

                                $url = new \moodle_url('/course/view.php', array('id' => (int) $activity->id));
                                $icon = \wa_learning_path\lib\get_course_icon((int) $activity->id);
                                $icon = empty($icon) ? '' : $icon;

                                $completionfilename = $activity->completed ? "/local/wa_learning_path/pix/m_completed.png" : "/local/wa_learning_path/pix/m_incomplete.png";
                            }else{
                                $title = $activity->title;
                                $description = \file_rewrite_pluginfile_urls($activity->description, 'pluginfile.php', $this->systemcontext->id, 'local_wa_learning_path', 'activity_description', $activity->id);
                                $url = new \moodle_url($this->url, array('c' => 'activity', 'a' => 'view', 'id' => (int) $activity->id));

                                $completionfilename = $activity->completed ? "/local/wa_learning_path/pix/a_completed.png" : "/local/wa_learning_path/pix/a_incomplete.png";
                            }

                            $completionicon = new \moodle_url($completionfilename);

                            $percentfilename = "/local/wa_learning_path/pix/percent{$activity->percent}.png";
                            $percentfile = new \moodle_url($percentfilename);

                            $methodologyfilename = "/local/wa_learning_path/pix/{$activity->methodology}.svg";
                            $methodologyfile = new \moodle_url($methodologyfilename);
                            ?>
                            <tr class="<?php if($first) echo "first"; $first = false; ?>">
                                <td class="cell c1">
                                    <?php echo $title ?>
                                    <?php echo $icon; ?><br /><?php echo $description; ?>
                                    <div class="clearfix"></div>
                                    <?php if($activity->type == 'module' && file_exists("$CFG->dirroot/$methodologyfilename")) : ?>
                                        <img src="<?php echo $methodologyfile ?>" alt="<?php echo $activity->methodology; ?>" class="item_icon" />
                                    <?php endif; ?>
                                </td>
                                <td class="cell c2">
                                    <?php if(file_exists("$CFG->dirroot/$percentfilename")): ?>
                                        <img src="<?php echo $percentfile ?>" alt="<?php echo $activity->percent; ?>" class="item_icon" />
                                    <?php endif; ?>
                                    <br />
                                    <span><?php echo $activity->percent; ?>%</span>
                                </td>
                                <td class="cell c3">
                                    <?php if($activity->type == 'module'): ?>
                                        <?php echo $activity->methodologyicon; ?>
                                        <br />
                                        <span><?php echo $activity->methodology; ?></span>
                                    <?php else: ?>
                                        <?php if(file_exists("$CFG->dirroot/$methodologyfilename")): ?>
                                            <img src="<?php echo $methodologyfile ?>" alt="<?php echo $activity->methodology; ?>" class="item_icon" />
                                             <br />
                                        <?php endif; ?>
                                        <span><?php echo $this->get_string('type_'.$activity->methodology); ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if($this->learning_path->subscribed): ?>
                                    <td class="cell c4">
                                        <?php if($activity->type == 'module'): ?>
                                            <?php if(file_exists("$CFG->dirroot/$completionfilename")): ?>
                                                <img src="<?php echo $completionicon ?>" alt="" class="" />
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if($activity->completed): ?>
                                                <img src="<?php echo $completionicon ?>" data-id="<?php echo (int) $activity->id ?>" alt="" class="activity_completion yes" />
                                            <?php else: ?>
                                                <img src="<?php echo $completionicon ?>" data-id="<?php echo (int) $activity->id ?>" alt="" class="activity_completion no" />
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
