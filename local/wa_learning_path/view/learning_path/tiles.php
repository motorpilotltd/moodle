<?php
if(empty($this->list)) {
    $this->display_error($this->get_string('no_results'), 'info');
    return;
}

$lpurl = new \moodle_url($this->url);

foreach ($this->list as $item) {
    $lpurl->param('id', (int) $item->id);
    $lpurl->param('a', ($item->subscribed) ? 'matrix' : 'view');
    require("$CFG->dirroot/local/wa_learning_path/view/learning_path/tiles_item.php");
}
echo html_writer::div('', 'clearfix');

?>
<script type='text/javascript'>
    $(document).ready(function(){
        $( '.tile .region' ).tooltip({
            position: {
//                my: "center right",
//                at: "right-10 top+60"
            }
        });
    })
</script>