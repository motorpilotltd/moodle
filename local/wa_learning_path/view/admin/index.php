<?php
global $USER, $OUTPUT, $CFG;
echo \html_writer::start_div('wa_learning_path_content');
echo $OUTPUT->heading($this->get_string('header_learning_path_list'));

$this->display_error($this->get_flash_massage('success'), 'success');
$this->display_error($this->get_flash_massage('error'), 'error');
$this->display_error($this->get_flash_massage('other'), 'other');


// add filters
$this->filtering->display_add();
$this->filtering->display_active();

if (\wa_learning_path\lib\has_capability('addlearningpath')) {
    echo "<button onclick='window.location=\"" . (new \moodle_url('/local/wa_learning_path/index.php', array('c' => 'admin', 'a' => 'edit'))) . "\"'>" . $this->get_string('create_learning_path') . '</button>';
}

if (empty($this->learningpathscount)) {
    echo \html_writer::tag('h4', $this->get_string('no_results'), array('class' => ''));
    echo \html_writer::end_div();
    return true;
}

$pluginname = 'local_wa_learning_path';

// Prepare table of learning paths list.
$this->table = new \html_table();
$this->table->id = 'lptab';
$this->table->head = array();
$this->table->colclasses = array('','','','','','nowrap');
$this->table->head[] = $this->get_string('learningpath_id', $pluginname);
$this->table->head[] = $this->get_string('learning_path', $pluginname);
$this->table->head[] = $this->get_string('category', $pluginname);
$this->table->head[] = $this->get_string('region', $pluginname);
$this->table->head[] = $this->get_string('status', $pluginname);
$this->table->head[] = $this->get_string('actions');

foreach ($this->learningpaths as $learningpath) {
    $buttons = array();
    if (\wa_learning_path\lib\has_capability('addlearningpath')) {
        $buttons[] = html_writer::link(new \moodle_url($this->url, array('a' => 'edit', 'id' => $learningpath->id)),
            html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'), 'alt' => $this->get_string('edit'), 'class' => 'iconsmall')),
            array('title' => $this->get_string('edit'), 'class' => ''));
        $buttons[] = html_writer::link(new \moodle_url($this->url, array('a' => 'duplicate', 'id' => $learningpath->id)),
            html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/copy'), 'alt' => $this->get_string('duplicate'), 'class' => 'iconsmall')),
            array('title' => $this->get_string('duplicate'), 'class' => ''));
    } elseif (\wa_learning_path\lib\has_capability('amendlearningcontent') || \wa_learning_path\lib\has_capability('editmatrixgrid')) {
        $buttons[] = html_writer::link(new \moodle_url($this->url, array('a' => 'edit_matrix', 'id' => $learningpath->id)),
            html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('i/edit'), 'alt' => $this->get_string('edit'), 'class' => 'iconsmall')),
            array('title' => $this->get_string('edit'), 'class' => ''));
    }

    if (\wa_learning_path\lib\has_capability('deletelearningpath')) {
        $buttons[] = html_writer::link(new \moodle_url($this->url, array('a' => 'delete', 'id' => $learningpath->id)),
            html_writer::empty_tag('img', array('src' => $OUTPUT->image_url('t/delete'), 'alt' => $this->get_string('delete'), 'class' => 'iconsmall')),
            array('class' => 'delete', 'title' => $this->get_string('delete')));
    }


    if (\wa_learning_path\lib\has_capability('publishlearningpath')) {
        $options = '<option value="'.WA_LEARNING_PATH_DRAFT.'" '.($learningpath->status == WA_LEARNING_PATH_DRAFT ? 'selected="1"' : '').'>'.get_string('draft', $pluginname).'</option>';
        $options .= '<option value="' . WA_LEARNING_PATH_PUBLISH . '" ' . ($learningpath->status == WA_LEARNING_PATH_PUBLISH ? 'selected="1"' : '') . '>' . get_string('publish', $pluginname) . '</option>';
        $options .= '<option value="'.WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE.'" '.($learningpath->status == WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE ? 'selected="1"' : '').'>'.get_string('publish_not_visible', $pluginname).'</option>';

        $status_change = "<select  class='".($learningpath->status == WA_LEARNING_PATH_DRAFT ? 'draft' : '').($learningpath->status == WA_LEARNING_PATH_PUBLISH ? 'publ' : '').($learningpath->status == WA_LEARNING_PATH_PUBLISH_NOT_VISIBLE ? 'invi' : '')."' lpid='".$learningpath->id."'>".$options."</select>";
    } else {
        if ($learningpath->status == WA_LEARNING_PATH_DRAFT) {
            $status_change = '<span class="draft">'.get_string('draft', $pluginname)."</span>";
        } else {
            if ($learningpath->status == WA_LEARNING_PATH_PUBLISH) {
                $status_change = '<span class="publ">'.get_string('publish', $pluginname)."</span>";
            } else {
                $status_change = '<span class="invi">'.get_string('publish_not_visible', $pluginname)."</span>";
            }
        }
    }

    $this->table->data[] = array(
        $learningpath->id,
        "<a href='".(new moodle_url('/local/wa_learning_path/?c=learning_path&a=view&id='.$learningpath->id))."'>".$learningpath->title.'</a>',
        $learningpath->category,
        isset($learningpath->regionsname) ? implode('<br />', $learningpath->regionsname) : $this->get_string('global'),
        $status_change,
        implode(' ', $buttons),
    );
}

echo \html_writer::start_tag('div', array('class' => 'no-overflow'));
echo \html_writer::table($this->table);
echo \html_writer::end_tag('div');
echo $OUTPUT->paging_bar($this->learningpathscount, $this->page, $this->perpage, $this->baseurl);

echo \html_writer::end_div();

?>

<div id="dialog-confirm" style="display: none;" title="<?php echo $this->get_string('confirm_delete') ?>">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo $this->get_string('confirm_message') ?></p>
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
        })

    	$('select[lpid]').change(function() {
            $('#lptab').css('opacity', 0.3);
            $.ajax({
                url: '<?php echo (new moodle_url('/local/wa_learning_path')); ?>/?c=admin&a=status&status='+$(this).val()+'&id='+$(this).attr('lpid'),
                success: function() {
                    $('#lptab').css('opacity', 1);
                }
            })

            if ($(this).val() == 1){
                $(this).removeAttr('class');
                $(this).addClass('draft');
            }
            else if ($(this).val() == 2){
                $(this).removeAttr('class');
                $(this).addClass('publ');
            }
            else if ($(this).val() == 3){
                $(this).removeAttr('class');
                $(this).addClass('invi');
            }


        })

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
