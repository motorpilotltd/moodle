<h2><?php echo $this->learning_path->title ?></h2>
<table>
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
//                        $id = '#' . $this->matrix->rows[$r]->id . '_' . $this->matrix->cols[$c]->id;
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
</table>