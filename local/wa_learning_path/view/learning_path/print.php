<h2 class="<?php if ($this->role) { echo "print-learning-journey"; } ?>"><?php echo $this->learning_path->title ?></h2>
<?php if ($this->role): ?>
<h2><?php echo $this->get_string('learning_journey') ?></h2>
<?php endif; ?>
<table class="<?php if ($this->role) { echo "print-learning-journey"; } ?>">
    <?php if ($this->role): ?>
        <thead>
        <th>
            <?php echo $this->get_string('skill_area'); ?>
        </th>
        <th>
            <?php echo $this->get_string('expected_level'); ?>
        </th>
        <th>
            <?php echo $this->get_string('role_specific_definition'); ?>
        </th>
        <th>
            <?php echo $this->get_string('essential_courses_and_activities'); ?>
        </th>
        </thead>
        <tbody>
        <?php foreach ($this->cellDesc as $hash => $cell):
                list($columnId, $rowId) = explode('_', $hash);
                $columnName = '';
                $rowName = '';
                foreach ($this->matrix->cols as $col) {
                    if ($col->id == $columnId) {
                        $columnName = $col->name;
                        break;
                    }
                }
                foreach ($this->matrix->rows as $row) {
                    if ($row->id == $rowId) {
                        $rowName = $row->name;
                        break;
                    }
                }

//                $this->change_activity_position($activity, $overridePosition, $this->activities->{$key}->positions);

                ?>
                <tr>
                    <td>
                        <?php echo $columnName ?>
                    </td>
                    <td>
                        <?php echo $rowName ?>
                    </td>
                    <td>
                        <?php echo format_text($cell['description']); ?>
                    </td>
                    <td>
                        <?php
                        foreach ($cell['activities'] as $activity):
                            if ($activity->position == 'essential'):
                                if ($activity->type == 'module'):
                                    $activityName = $activity->fullname;
                                elseif ($activity->type == 'activity'):
                                    $activityName = $activity->title;
                                endif;

                                $completionFilename = $activity->completed ? "/local/wa_learning_path/pix/a_completed.png" : "/local/wa_learning_path/pix/a_incomplete.png";
                                $completionIcon = new \moodle_url($completionFilename);
                                ?>

                                <img src="<?php echo $completionIcon ?>" alt="" class="" />
                                <span><?php echo $activityName; ?></span><br>

                            <?php endif; ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    <?php else: ?>
        <thead>
            <th>

            </th>
            <?php
            for ($c = 0; $c < count($this->matrix->cols); $c++) {
                if ($this->matrix->cols[$c]->show) {
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
                    <td>
                        <?php
                        $id = '#' . $this->matrix->cols[$c]->id . '_' . $this->matrix->rows[$r]->id;
                        if (isset($this->matrix->activities->{$id}) && $this->matrix->activities->{$id}->content) {
                            echo strip_tags($this->matrix->activities->{$id}->content);
                        }
                        ?>
                    </td>
                <?php } endfor; ?>
            </tr>
            <tr>
                <td>
                    <?php echo $this->get_string('essential') ?>
                </td>
                <?php for ($c = 0; $c < count($this->matrix->cols); $c++): if ($this->matrix->cols[$c]->show) { $id = '#' . $this->matrix->cols[$c]->id . '_' . $this->matrix->rows[$r]->id; ?>
                    <td><?php echo (int)@$this->matrix->activities->{$id}->positions->essential > 0 ? count(@$this->matrix->activities->{$id}->positions->essential) : '0'; ?></td>
                <?php } endfor; ?>
            </tr>
            <tr>
                <td>
                    <?php echo $this->get_string('recommended') ?>
                </td>
                <?php for ($c = 0; $c < count($this->matrix->cols); $c++): if ($this->matrix->cols[$c]->show) { $id = '#' . $this->matrix->cols[$c]->id . '_' . $this->matrix->rows[$r]->id; ?>
                    <td><?php echo (int)@$this->matrix->activities->{$id}->positions->recommended > 0 ? count(@$this->matrix->activities->{$id}->positions->recommended) : '0'; ?></td>
                <?php } endfor; ?>
            </tr>
            <tr>
                <td>
                    <?php echo $this->get_string('elective') ?>
                </td>
                <?php for ($c = 0; $c < count($this->matrix->cols); $c++): if ($this->matrix->cols[$c]->show) { $id = '#' . $this->matrix->cols[$c]->id . '_' . $this->matrix->rows[$r]->id; ?>
                    <td><?php echo (int)@$this->matrix->activities->{$id}->positions->elective > 0 ? count(@$this->matrix->activities->{$id}->positions->elective) : '0'; ?></td>
                <?php } endfor; ?>
            </tr>
        <?php } endfor; ?>
        </tbody>
    <?php endif; ?>
</table>