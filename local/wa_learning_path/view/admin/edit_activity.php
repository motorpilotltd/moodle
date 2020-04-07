<?php if ($this->id && \wa_learning_path\lib\has_capability('editlearningmatrix')): ?>
    <div class="wa_tabs">
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit&id='.$this->id)); ?>"><?php echo $this->get_string('tab_introduction') ?></a>
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit_matrix&id='.$this->id)); ?>" class="current"><?php echo $this->get_string('tab_matrix') ?></a>
        <a href="<?php echo (new moodle_url('/local/wa_learning_path/?c=admin&a=edit_subscriptions&id='.$this->id)); ?>"><?php echo $this->get_string('tab_subscriptions') ?></a>
    </div>
<?php endif;

$this->form->display();

?>