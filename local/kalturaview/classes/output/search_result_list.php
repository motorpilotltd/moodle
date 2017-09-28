<?php

namespace local_kalturaview\output;

/**
 * Created by PhpStorm.
 * User: paulstanyer
 * Date: 16/11/2016
 * Time: 12:03
 */
class search_result_list implements \templatable, \renderable
{
    private $results;
    private $totalCount;

    public function __construct(\KalturaMediaListResponse $searchresults, $searchterm)
    {
        global $OUTPUT;

        $this->totalCount = $searchresults->totalCount;

        $partnerid = get_config(KALTURA_PLUGIN_NAME, 'partner_id');


        foreach ($searchresults->objects as $result) {
            $renderable = new search_result($result, $searchterm, $partnerid);
            $this->results[] = $renderable->export_for_template($OUTPUT);
        }
    }

    public function export_for_template(\renderer_base $output)
    {
        $selectedSort = optional_param('sort', '-weight', PARAM_ALPHAEXT);

        $sortoptions = [
            ['name' => 'Best Match', 'value' => '-weight', 'selected' => ($selectedSort=='-weight')],
            ['name' => 'Popularity', 'value' => '-plays', 'selected' => ($selectedSort=='-plays')],
            ['name' => 'Best Rated', 'value' => '-rank', 'selected' => ($selectedSort=='-rank')],
        ];

        return array(
            'sortoptions' => $sortoptions,
            'resultcount' => $this->totalCount,
            'results' => $this->results
        );
    }
}