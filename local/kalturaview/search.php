<?php

use local_kalturaview\output;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login(SITEID, false);
require_capability('local/search:view', context_system::instance());

require_once($CFG->dirroot . '/local/kaltura/locallib.php');

$searchterm = optional_param('search', '', PARAM_TEXT);

$PAGE->set_url(new moodle_url('/local/kalturaview/search.php'));

$PAGE->set_pagelayout('base');

$renderer = $PAGE->get_renderer('local_kalturaview');

$client = arup_local_kaltura_get_kaltura_client(get_config('local_kalturaview', 'privileges'), get_config('local_kalturaview', 'sessionexpires'));

$pager = new KalturaFilterPager();
$pager->pageIndex = optional_param('page', 1, PARAM_INT);
$pager->pageSize = optional_param('perpage', get_config('local_kalturaview', 'resultsperpage'), PARAM_INT);

$filter = new KalturaMediaEntryFilter();
$filter->categoryAncestorIdIn = get_config('local_kalturaview', 'categoryids');
$filter->freeText = implode('*,', explode(' ', $searchterm)) . '*';

//$filter->tagsAdminTagsNameMultiLikeOr = implode(',', explode(' ', $searchterm));
$filter->orderBy = optional_param('sort', '-weight', PARAM_ALPHAEXT);

// Add order by box
$results = $client->media->listAction($filter, $pager);

$renderable = new \local_kalturaview\output\search_result_list($results, $searchterm);
//

echo $renderer->search_results($renderable);