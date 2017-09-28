<?php

require_once("../../config.php");
require_once("lib.php");

$regionid = optional_param('regionid', 0, PARAM_INT);

$subregions = local_learningpath_filter_block::filtered_sub_regions($regionid);
foreach ($subregions as $id => $name) {
    echo '<option value="'.$id.'">'.$name.'</option>' . "\n";
}
