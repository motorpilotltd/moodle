<?php
global $USER, $OUTPUT, $CFG, $PAGE;
    
    $preview = optional_param('preview', 0, PARAM_INT);

echo \html_writer::start_div('learning_path');
$heading = $this->learning_path->title;
if(!$this->preview) {
    $heading .= $this->subscribeButton;
}
echo $OUTPUT->heading($heading);

$this->display_error($this->get_flash_massage('success'), 'success');
$this->display_error($this->get_flash_massage('error'), 'error');
$this->display_error($this->get_flash_massage('other'), 'other');
$preview = optional_param('preview', 0, PARAM_INT);

$subscribeurl = new \moodle_url(
    '/local/wa_learning_path/index.php',
    array(
        'c' => 'learning_path',
        'a' => ($this->learning_path->subscribed) ? 'unsubscribe' : 'subscribe',
        'id' => $this->learning_path->id
    )
);
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

<div class="clearfix"></div>
<div class="learning_path_content">
	<div class="left"><?php echo format_text($this->learning_path->introduction); ?></div>
	<div class="right">
        <div class="details">
            <?php echo \wa_learning_path\model\learningpath::get_image_url($this->learning_path->id, true) ?>
            <div class="keywords_tags">
                <div><?php echo $this->get_string('keywords_tags') ?></div>
                <div class="keywords_tags_list">
                    <?php foreach($this->learning_path->keywords_list as $keyword): ?>
                        <div class="keyword"><?php echo $keyword; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <br>
        <a class="btn  wa-btn btn-primary  goto_learning_path_matrix" href="<?php echo $this->matrixurl ?>"><?php echo $this->get_string('view_learning_path') ?></a>
        <div class="clearfix"></div>
	</div>
	<!--<div class="clearfix"></div>-->

    
</div>
<div class="clearfix"></div>
<?php
echo \html_writer::end_div();
