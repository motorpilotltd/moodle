<a name="activities_tab"></a>
<div class="matrix_activities padd_5">
    <?php if (\wa_learning_path\lib\has_capability('printlearningmatrix')): ?>
        <a id='print_section' type="button" href="<?php echo new \moodle_url($this->url, array('a' => 'print_section', 'id' => $this->id, 'ajax' => 1, 'key' => str_replace('#', '', $this->key))) ?>" class="btn btn-default fl-r"><?php echo $this->get_string('print_section') ?></a>
    <?php endif; ?>

    <h2><?php echo $this->r_label ?>, <?php echo $this->c_label ?></h2>
    <p><?php echo $this->cell->content ?></p>
    <br />
    <form class="filtration">
        <div class="filter_checkboxes filter_position clearfix">
            <input type="checkbox" id="position_essential" value="essential" name="position[]" checked>
            <label for="position_"><?php echo $this->get_string('essential') ?></label>

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
    <?php if(!empty($this->position)): $first = true; ?>
        <div class="tab_content <?php if($this->count[$this->position] > 10): ?>add_scroll<?php endif; ?>">
            <div class="no-overflow">
            <?php if($this->activities && !empty($this->count[$this->position])): ?>
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
                                <span class="bg-primary activity-position"><?php echo $this->get_string($activity->position); ?></span>
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
                                <?php if(file_exists("$CFG->dirroot/$percentfilename")): ?>
                                    <img src="<?php echo $percentfile ?>" alt="<?php echo $activity->percent; ?>" class="item_icon" />
                                <?php endif; ?>
                                    <br />
                                <span><?php echo $activity->percent; ?>%</span>
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
                                        <?php if($activity->completed): ?>
                                            <img src="<?php echo $completionicon ?>" data-id="<?php echo (int) $activity->id ?>" alt="" class="activity_completion yes" />
                                        <?php else: ?>
                                            <img src="<?php echo $completionicon ?>" data-id="<?php echo (int) $activity->id ?>" data-cpd="<?php global $DB; echo (int) $DB->get_field('wa_learning_path_activity', 'enablecdp', array('id' => $activity->id)); ?>" alt="" class="activity_completion no" />
                                            <div class="mark_as_complete"><?php echo $this->get_string('mark_as_complete') ?></div>
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

<script type='text/javascript'>
    $(document).ready(function(){
        $('.matrix_activities .activity_completion').click(function(e){
            var status = $(this).hasClass('no') ? 1 : 0;
            var activityid = $(this).attr('data-id');

            // If completing and data-cpd is 1 need to confirm.
            if (status && $(this).data('cpd')) {
                var result = window.confirm('<?php print_string('confirm:cpdupload', 'local_wa_learning_path') ?>');
                if (!result) {
                    e.preventDefault();
                    return false;
                }
            }

            $.ajax({
                method: "POST",
                url: "<?php $url =  new \moodle_url($this->url, array('c' => 'activity', 'a' => 'set_completion')); echo $url->out(false); ?>",
                data: { activityid: activityid, learningpathid: <?php echo (int) $this->id ?> ,completion: status }
              })
            .done(function( msg ) {
                window.location.reload();
            });
            return false;
        });

        $('#print_section').click(function(){
            var levels = '';
            var regions = '';

            if($('.wa_learning_path_nav_content .levels').val()) {
                levels = $('.wa_learning_path_nav_content .levels').val().join();
            }

            if($('.wa_learning_path_nav_content .region').val()) {
                regions = $('.wa_learning_path_nav_content .region').val().join();
            }

            var url = $(this).prop('href') + '&levels=' + levels + '&regions=' + regions;
            $(this).prop('href', url);
        });

        $('form.filtration :checkbox, form.filtration select').on('change', function() {
            updateFiltered();
        });

        // Initial filtering.
        updateFiltered();
    });

    var updateFiltered = function() {
        var positions = $('form.filtration .filter_position :checkbox:checked').map(function(){
            return $(this).val();
        }).get();
        var percents = $('form.filtration .filter_percent :checkbox:checked').map(function(){
            return parseInt($(this).val());
        }).get();
        var methodology = $('form.filtration .filter_methodology select').val();

        $('table.list_of_activities tr').each(function(){
            if (methodology !== '' && $(this).data('methodology') !== methodology) {
                $(this).hide();
                // Continue to next row.
                return;
            }
            if ($.inArray($(this).data('position'), positions) === -1) {
                $(this).hide();
                // Continue to next row.
                return;
            }
            if ($.inArray(parseInt($(this).data('percent')), percents) === -1) {
                $(this).hide();
                // Continue to next row.
                return;
            }
            // All match so show element.
            $(this).show();
        });

        $('table.list_of_activities tr').removeClass('first').filter(':visible:first').addClass('first');
    }
</script>
