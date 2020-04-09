<?php
global $CFG;
?>
<a name="activities_tab"></a>
<div class="matrix_activities padd_5" >
    <div class="matrix-activities-description">
        <?php
            if(!empty($this->matrix->cols[$this->previousColumn]->name)  && !is_null($this->previousColumn)) {
                $this->cell_url->param('key', '#' . $this->matrix->cols[$this->previousColumn]->id . '_' . $this->rowId);
                echo "<button class=\"navigate-cells column-left\" data-url=\"".$this->cell_url->out(true)."\">";
    
                $wrappedText = wordwrap(htmlentities(html_entity_decode(($this->matrix->cols[$this->previousColumn]->name))), 25, '<br>');
                $wraps = explode('<br>', $wrappedText);
                $text = implode('<br>', array_splice($wraps,0,2));
                
                echo "<span class=\"modal-text-left\">".$this->get_icon_html('icon_modal_navigation_icon')."<br>".$text."</span>";
                echo "</button>";
            }
        ?>
        <div class="matrix-activities-description-text">
            <?php if (\wa_learning_path\lib\has_capability('printlearningmatrix')): ?>
                <?php $params = array('a' => 'print_section', 'id' => $this->id, 'ajax' => 1, 'key' => str_replace('#', '', $this->key)); ?>
                <?php if (isset($this->role)): ?>
                    <?php $params['role'] = $this->role; ?>
                <?php endif; ?>

                <?php if ($this->regions): ?>
                    <?php $params['regions'] = implode(',', $this->regions); ?>
                <?php endif; ?>
                <a id='print_section' type="button" href="<?php echo new \moodle_url($this->url, $params) ?>" class="btn button-print btn-default pull-right"><?php echo $this->get_icon_html('icon_print') . $this->get_string('print_section') ?></a>
            <?php endif; ?>

            <?php if (isset($this->activities->{$this->key})): ?>
                <span class="matrix-activities-title" data-rlabel="<?php echo $this->r_label ?>" data-clabel="<?php echo $this->c_label ?>"><?php echo $this->r_label ?>, <?php echo $this->c_label ?>
                    <?php if (isset($this->headerTooltip) && $this->headerTooltip): ?>
                        <a class="btn-link help" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;><p><?php echo $this->headerTooltip; ?></p>
                        </div> " data-html="true" tabindex="0" data-trigger="focus">
                            <img class="icon " alt="<?php echo $this->get_string('description'); ?>" title="<?php echo $this->headerTooltip; ?>" src="<?php echo $OUTPUT->image_url('help', 'moodle')->out(false); ?>">
                        </a>
                    <?php endif; ?>
                </span>
                <?php if (isset($this->role)): ?>
                <br/>
                <span class="matrix-activities-role <?php if (isset($this->roleName)): echo 'enabled-role'; endif; ?>"><?php if (isset($this->roleName)): echo $this->roleName; endif; ?></span><br/>
                <?php endif; ?>
                <?php if ($this->cellData && $this->cellData['description']): ?>
                    <p><?php echo format_text($this->cellData['description']) ?></p>
                <?php else: ?>
                    <p><?php echo format_text($this->cell->content) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="alert alert-warning"><?php echo $this->get_string('no_objectives_defined'); ?></p>
            <?php endif; ?>

        </div>
            <?php
                if(!empty($this->matrix->cols[$this->nextColumn]->name) && !is_null($this->nextColumn)){
                    $this->cell_url->param('key',  '#' . $this->matrix->cols[$this->nextColumn]->id . '_' . $this->rowId);
                    echo "<button class=\"navigate-cells column-right\" data-url=\"".$this->cell_url->out(true)."\">";
    
                    $wrappedText = wordwrap(htmlentities(html_entity_decode(($this->matrix->cols[$this->nextColumn]->name))), 25, '<br>');
                    $wraps = explode('<br>', $wrappedText);
                    $text = implode('<br>', array_splice($wraps,0,2));
                    
                    echo "<span class=\"modal-text-right\">".$this->get_icon_html('icon_modal_navigation_icon')."<br>".$text."</span>";
                    echo "</button>";
                }
            ?>
    </div>
    <br />
    <?php if($this->activities && isset($this->count) && !empty($this->count[$this->position])): ?>
    <form class="filtration">
        <div class="filter_checkboxes filter_position clearfix">
            <input type="checkbox" id="position_essential" value="essential" name="position[]" checked>
            <label for="position_essential"><?php echo $this->get_string('essential') ?></label>

            <input type="checkbox" id="position_recommended" value="recommended" name="position[]" checked>
            <label for="position_recommended"><?php echo $this->get_string('recommended') ?></label>

            <input type="checkbox" id="position_elective" value="elective" name="position[]" checked>
            <label for="position_elective"><?php echo $this->get_string('elective') ?></label>
        </div>
        <div class="filter_checkboxes filter_percent pull-left">
            <input type="checkbox" id="percent_70" value="70" name="percent[]" checked>
            <label for="percent_70">70</label>

            <input type="checkbox" id="percent_20" value="20" name="percent[]" checked>
            <label for="percent_20">20</label>

            <input type="checkbox" id="percent_10" value="10" name="percent[]" checked>
            <label for="percent_10">10</label>
        </div>
        <div class="filter_methodology pull-left">
            <label><?php echo $this->get_string('filter_by_methodology') ?></label>
            <select name="methodology">
                <option value="" selected="selected"><?php echo $this->get_string('all') ?></option>
                <?php foreach($this->methodologylist as $key => $type): ?>
                    <option value="<?php echo $key ?>"><?php echo $type ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if($this->learning_path->subscribed): ?>
            <div class="pull-right is_completed"><?php echo $this->get_string('is_completed') ?></div>
        <?php endif; ?>
        <div class="clearfix"></div>
    </form>
    <?php endif; ?>


    <?php if(!empty($this->position)): $first = true; ?>
        <div class="tab_content <?php if(isset($this->count) && $this->count[$this->position] > 10): ?>add_scroll<?php endif; ?>">
            <div class="no-overflow">
            <?php if($this->activities && isset($this->count) && !empty($this->count[$this->position])): ?>
                <table class="list_of_activities">
                    <tbody>
                        <?php foreach($this->cell->positions->{$this->position} as $iterator => $activity): ?>
                        <?php
                            if(empty($activity->id)){
                                continue;
                            }

                            if (!wa_learning_path\model\learningpath::check_regions_match($this->regions, $activity->region)) {
                                continue;
                            }

                            if ($this->cellData) {
                                if (!$this->is_visible($activity->id, $activity->type)) {
                                    continue;
                                }
                            }

                            $id = $iterator . '_' . $activity->type . "_" . $activity->id;
                            $icon = '';
                            if ($activity->type == 'module') {

                                $title = $activity->fullname;
                                $coursecontext = context_course::instance($activity->id);
                                $options = array();
                                $summary = file_rewrite_pluginfile_urls($activity->description, 'pluginfile.php', $coursecontext->id, 'course', 'summary', null);
                                $description = format_text($summary, $activity->summaryformat, $options, $activity->id);

                                $url = new \moodle_url('/course/view.php', array('id' => (int) $activity->id));
                                $icon = \wa_learning_path\lib\get_course_icon((int) $activity->id);
                                $icon = empty($icon) ? '' : $icon;

                                $completionfilename = $activity->completed ? "/local/wa_learning_path/pix/m_completed.png" : "/local/wa_learning_path/pix/m_incomplete.png";
                            } else {
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
                        <tr class="<?php if($first) echo "first"; $first = false; ?>"
                            data-position="<?php echo $activity->position; ?>" data-percent="<?php echo $activity->percent; ?>" data-methodology="<?php echo $activity->methodology; ?>" >
                            <td class="cell c0">
                                <span class="activity-position <?php echo 'position-' . $activity->position; ?>"><?php echo $this->get_string($activity->position); ?></span>
                            </td>
                            <td class="cell c1">
                                <h4 class="activity_title"><?php echo $title ?></h4>
                                <div class="activity_full_description" id="description_<?php echo $id ?>">
                                    <?php echo $description ?>
                                    <div class="clearfix"></div>
                                    <?php if ($activity->type == 'module' && $this->learning_path->subscribed) : ?>
                                        <a class="btn btn-primary" href="<?php echo $url->out() ?>"><?php echo $this->get_string('open_module') ?></a>
                                    <?php elseif ($activity->type == 'module') : ?>
                                        <button class="btn btn-primary disabled"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                title="<?php echo $this->get_string('open_module_tooltip'); ?>">
                                            <?php echo $this->get_string('open_module') ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="cell c2">
                            <?php if (!empty($activity->percent)): ?>
                                <?php if(file_exists("$CFG->dirroot/$percentfilename")): ?>
                                    <img src="<?php echo $percentfile ?>" alt="<?php echo $activity->percent; ?>" class="item_icon" />
                                <?php endif; ?>
                                    <br />
                                <span><?php echo $activity->percent; ?>%</span>
                            <?php endif; ?>
                            </td>
                            <td class="cell c3">
                                <?php if($activity->type == 'module'): ?>
									<?php echo $activity->methodologyicon; ?>
                                    <div><?php echo $activity->methodology; ?></div>
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
                                        <?php $url =  new \moodle_url($this->url, array('c' => 'activity', 'a' => 'set_completion'));  ?>
                                        <?php global $DB; $cpd = (int) $DB->get_field('wa_learning_path_activity', 'enablecdp', array('id' => $activity->id)); ?>
                                        <?php if($activity->completed): ?>
                                            <img src="<?php echo $completionicon ?>" data-url="<?php echo $url->out(false);?>" data-lpathid="<?php echo $this->learning_path->id; ?>" data-id="<?php echo (int) $activity->id ?>" data-cpd="<?php echo $cpd ?>" alt="" class="activity_completion yes" />
                                        <?php else: ?>
                                            <img src="<?php echo $completionicon ?>" data-url="<?php echo $url->out(false);?>"  data-lpathid="<?php echo $this->learning_path->id; ?>" data-id="<?php echo (int) $activity->id ?>" data-cpd="<?php echo $cpd ?>" alt="" class="activity_completion no" />
                                            <div class="mark_as_complete"><?php echo $this->get_string('mark_as_complete') ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="alert alert-warning"><?php echo $this->get_string('no_activities_defined'); ?></p>
            <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
