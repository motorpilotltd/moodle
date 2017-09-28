<?php
global $USER, $OUTPUT, $CFG;
echo \html_writer::start_div('wa_learning_path_nav_content');
echo $OUTPUT->heading($this->get_string('header_category_settings'));

require_once($CFG->dirroot . '/blocks/wa_learning_path_nav/lib/image.class.php');

$this->display_error($this->get_flash_massage('success'), 'success');
$this->display_error($this->get_flash_massage('error'), 'error');
$this->display_error($this->get_flash_massage('other'), 'other');


foreach ($this->categories as $category) {
    $buttons = array();
    $buttons[] = html_writer::link(new \moodle_url($this->url, array('a' => 'settings', 'categoryid' => $category->id)),
                    $this->get_string('change_settings'),
                    array('title' => $this->get_string('change_settings'), 'class' => 'btn'));
    
    $imgurl = null;
    if (empty($category->nologo)) {
        $imgurl = \wa_learning_path_nav\lib\wa_image::get_file_url(\context_system::instance(), $category->logoid,
                        \wa_learning_path_nav\lib\wa_image::get_image_file_name($category->logoid));
    }

    $this->table->data[] = array(
        $category->name,
        empty($imgurl) ? $this->get_string('no_logo') : html_writer::empty_tag('img',
                        array('src' => $imgurl, 'alt' => $category->name . ' - ' .$this->get_string('logo'), 'class' => 'logo current_logo')),
        implode(' ', $buttons),
    );
}

// add filters
$this->filtering->display_add();
$this->filtering->display_active();

if (empty($this->categoriescount)) {
    echo \html_writer::tag('h4', $this->get_string('no_results'), array('class' => ''));
    return true;
}

echo \html_writer::start_tag('div', array('class' => 'no-overflow'));
echo \html_writer::table($this->table);
echo \html_writer::end_tag('div');
echo $OUTPUT->paging_bar($this->categoriescount, $this->page, $this->perpage, $this->baseurl);

echo \html_writer::end_div();

?>
