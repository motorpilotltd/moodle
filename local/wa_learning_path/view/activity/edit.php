<?php
global $USER, $OUTPUT, $CFG;
echo \html_writer::start_div('wa_learning_path_content');
echo $OUTPUT->heading($this->title);

$this->display_error($this->get_flash_massage('success'), 'success');
$this->display_error($this->get_flash_massage('error'), 'error');
$this->display_error($this->get_flash_massage('other'), 'other');

$this->form->display();

echo \html_writer::end_div();
?>
<script>
    $(document).ready(function () {
    });
</script>