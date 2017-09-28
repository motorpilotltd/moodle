<?php

namespace local_kalturaview\output;

/**
 * Created by PhpStorm.
 * User: paulstanyer
 * Date: 16/11/2016
 * Time: 12:03
 */
class search_result implements \templatable, \renderable
{
    private $result;
    private $searchterm;
    private $partnerid;

    public function __construct(\KalturaMediaEntry $result, $searchterm, $partnerid)
    {
        $this->result = $this->safe_descriptions($result);
        $this->searchterm = $searchterm;
        $this->partnerid = $partnerid;
    }

    /**
     * Remove any HTML that would break layout
     *
     * @param \KalturaMediaEntry $result A media object with description
     * @return \KalturaMediaEntry
     */
    public function safe_descriptions(\KalturaMediaEntry $result)
    {
        // remove overflow hidden from style directly on element
        $result->description = strip_tags($result->description, 'em strong b i br hr p');

        return $result;
    }

    public function export_for_template(\renderer_base $output)
    {
        $returnurl = new \moodle_url('/local/search/index.php', array (
            'search' => $this->searchterm,
        ), 'tab-content-kaltura');

        return array(
            'uri' => new \moodle_url('/local/kalturaview/view.php', array(
                        'id' => $this->result->id,
                        'return' => $returnurl)),
            'title' => highlight($this->searchterm, $this->result->name),
            'description' => highlight($this->searchterm, $this->result->description),
            'thumburi' => "https://cdnapisec.kaltura.com/p/{$this->partnerid}/thumbnail/entry_id/{$this->result->id}/vid_slices/10/type/2/width/230/height/130",
            'creatorId' => $this->result->creatorId,
            'createdAt' => $this->result->createdAt,
            'views' => $this->result->views,
            'plays' => $this->result->plays,
            'votes' => $this->result->votes,
            'tags' => explode(',',$this->result->tags),
            'hastags' => !empty($this->result->tags)
        );
    }
}