<?php if (isset($this->id)): ?>
    <div class="wa_tabs">
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit&id='.$this->id)); ?>" class="current"><?php echo $this->get_string('tab_introduction') ?></a>
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit_matrix&id='.$this->id)); ?>"><?php echo $this->get_string('tab_matrix') ?></a>
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit_subscriptions&id='.$this->id)); ?>"><?php echo $this->get_string('tab_subscriptions') ?></a>
    </div>
<?php endif; ?>

<div id="editing">
    <?php
    $this->display_error($this->get_flash_massage('success'), 'success');
    $this->display_error($this->get_flash_massage('error'), 'error');
    $this->display_error($this->get_flash_massage('other'), 'other');

    global $OUTPUT;
    $this->form->display();
    ?>
</div>