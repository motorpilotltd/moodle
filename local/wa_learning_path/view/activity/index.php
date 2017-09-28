<?php
global $USER, $OUTPUT, $CFG;
echo \html_writer::start_div('wa_learning_path_content');
echo $OUTPUT->heading($this->get_string('header_learning_path_activity_list'));

$this->display_error($this->get_flash_massage('success'), 'success');
$this->display_error($this->get_flash_massage('error'), 'error');
$this->display_error($this->get_flash_massage('other'), 'other');


// add filters
$this->filtering->display_add();
$this->filtering->display_active();
if (\wa_learning_path\lib\has_capability('addactivity')) {
    echo \html_writer::link(new \moodle_url($this->url, array('a' => 'edit')), $this->get_string('create_new_activity'), array('class' => 'btn btn-default'));
}

if (empty($this->count)) {
    echo \html_writer::tag('h4', $this->get_string('no_results'), array('class' => ''));
    echo \html_writer::end_div();
    return true;
}

$pluginname = 'local_wa_learning_path';

// Prepare table of activities list.
$this->table = new \html_table();
$this->table->id = 'lptab';
$this->table->head = array();
$this->table->colclasses = array();
$this->table->head[] = $this->get_string('activity_id', $pluginname);
$this->table->head[] = $this->get_table_header('title', $this->get_string('activity_title', $pluginname), $this->sort, $this->dir, 'activity');
$this->table->head[] = $this->get_table_header('learningpath', $this->get_string('learning_path', $pluginname), $this->sort, $this->dir, 'activity');
$this->table->head[] = $this->get_table_header('region_name', $this->get_string('region', $pluginname), $this->sort, $this->dir, 'activity');
$this->table->head[] = $this->get_table_header('type', $this->get_string('activity_type', $pluginname), $this->sort, $this->dir, 'activity');
$this->table->head[] = $this->get_string('actions');

foreach ($this->list as $activity) {
    $buttons = array();
    if (\wa_learning_path\lib\has_capability('editactivity')) {
        $buttons[] = html_writer::link(new \moodle_url($this->url, array('a' => 'edit', 'id' => $activity->id)),
            html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/edit'), 'alt' => $this->get_string('edit'), 'class' => 'iconsmall')),
            array('title' => $this->get_string('edit'), 'class' => ''));
    }
    if (\wa_learning_path\lib\has_capability('deleteactivity')) {
        $buttons[] = html_writer::link(new \moodle_url($this->url, array('a' => 'delete', 'id' => $activity->id)),
            html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => $this->get_string('delete'), 'class' => 'iconsmall')),
            array('class' => 'delete', 'title' => $this->get_string('delete')));
    }
    
    $buttons[] = html_writer::link(new \moodle_url($this->url, array('a' => 'view', 'id' => $activity->id)),
            html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/preview'), 'alt' => $this->get_string('view'), 'class' => 'iconsmall')),
            array('class' => 'view', 'title' => $this->get_string('view')));

    $this->table->data[] = array(
        $activity->id,
        $activity->title,
        $activity->learningpath,
        implode(', ', $activity->regionsname),
        $this->get_string('type_' . $activity->type),
        implode(' ', $buttons),
    );
}

echo \html_writer::start_tag('div', array('class' => 'no-overflow'));
echo \html_writer::table($this->table);
echo \html_writer::end_tag('div');
echo $OUTPUT->paging_bar($this->count, $this->page, $this->perpage, $this->baseurl);

echo \html_writer::end_div();

?>

<div id="dialog-confirm" style="display: none;" title="<?php echo $this->get_string('confirm_delete') ?>">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo $this->get_string('activity_delete_confirm') ?></p>
</div>

<div id="dialog-cant-delete" style="display: none;" title="<?php echo $this->get_string('confirm_delete') ?>">
    <span ></span>
</div>

<script type='text/javascript'>
    var dialog_confirm;
    var delete_url;
    $(function () {
        $('.delete').click(function () {
            delete_url = $(this).attr('href');
            dialog_confirm.dialog("open");
            return false;
        });

        dialog_confirm = $("#dialog-confirm").dialog({
            resizable: false,
            height: 200,
            width: 500,
            modal: true,
            autoOpen: false,
            buttons: {
                "<?php echo $this->get_string('confirm_delete') ?>": function () {
                    window.location.href = delete_url;
                },
                "<?php echo $this->get_string('no') ?>": function () {
                    $(this).dialog("close");
                },
            }
        });
    });
</script>
