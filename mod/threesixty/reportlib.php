<?php

function print_aggregate_score_table($threesixty, $skillnames, $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage, $return = false) {
    $showaverage = $showrespondentaverage && !empty($respondentscores);
    $table = new html_table();
    $table->attributes['class'] = 'generaltable threesixty-report-table';

    $header = array(
        threesixty_get_alternative_word($threesixty, 'competency'),
    );
    $alignment = array('left');
    foreach (array_keys($selfscores) as $type) {
        $thisheader = get_string('yourscore', 'threesixty');
        if (count($selfscores) > 1) {
            $thisheader .= " [{$selftypes[$type]}]";
        }
        $header[] = get_score_table_header($thisheader);
        $alignment[] = 'center';
    }
    foreach ($respondentscores as $type => $respondentscoresbytype) {
        foreach ($respondentscoresbytype as $respondentscore) {
            $header[] = get_score_table_header($respondentscore->firstname.' '.$respondentscore->lastname);
            $alignment[] = 'center';
        }
    }
    if ($showaverage) {
        $header[] = get_score_table_header(get_string('respondentaverage', 'threesixty'));
        $alignment[] = 'center';
    }
    $table->head = $header;
    $table->align = $alignment;

    $competencies = get_aggregate_scores($threesixty, $skillnames, $selfscores, $respondentscores);

    foreach ($competencies as $competency) {
        $cells = array($competency->name);
        foreach ($competency->scores as $score) {
            $cells[] = $score;
        }
        if ($showaverage) {
            $cells[] = sprintf('%01.2f', $competency->respondentaverage);
        }
        $row = new html_table_row();
        if (!empty($competency->colour)) {
            list($r, $g, $b) = sscanf($competency->colour, "#%02x%02x%02x");
            $perceiveddarkness = 1 - ( 0.299 * $r + 0.587 * $g + 0.114 * $b)/255;
            if ($perceiveddarkness < 0.5) {
                $color = '#000000';
            } else {
                $color = '#FFFFFF';
            }
            $row->style = "color:{$color};background-color:{$competency->colour};";
        }
        $row->cells = $cells;
        $table->data[] = $row;
    }

    if ($return) { return $table; }

    echo html_writer::table($table);
}

function print_skill_score_table($threesixty, $skillnames, $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage, $return = false) {
    $showaverage = $showrespondentaverage && !empty($respondentscores);
    $table = new html_table();
    $table->attributes['class'] = 'generaltable threesixty-report-table';

    $header = array(
        '',
        threesixty_get_alternative_word($threesixty, 'skill'),
    );
    $alignment = array(null, 'left');
    foreach (array_keys($selfscores) as $type) {
        $thisheader = get_string('yourscore', 'threesixty');
        if (count($selfscores) > 1) {
            $thisheader .= " [{$selftypes[$type]}]";
        }
        $header[] = get_score_table_header($thisheader);
        $alignment[] = 'center';
    }
    foreach ($respondentscores as $type => $respondentscoresbytype) {
        foreach ($respondentscoresbytype as $respondentscore) {
            $header[] = get_score_table_header($respondentscore->firstname.' '.$respondentscore->lastname);
            $alignment[] = 'center';
        }
    }
    if ($showaverage) {
        $header[] = get_score_table_header(get_string('respondentaverage', 'threesixty'));
        $alignment[] = 'center';
    }
    $table->head = $header;
    $table->align = $alignment;

    $skillcount = 0;
    foreach ($skillnames as $skillname) {
        $cells = array();
        $skillcount++;
        $cell = new html_table_cell($skillcount);
        if (!empty($skillname->competencycolour)) {
            list($r, $g, $b) = sscanf($skillname->competencycolour, "#%02x%02x%02x");
            $perceiveddarkness = 1 - ( 0.299 * $r + 0.587 * $g + 0.114 * $b)/255;
            if ($perceiveddarkness < 0.5) {
                $color = '#000000';
            } else {
                $color = '#FFFFFF';
            }
            $cell->style = "color:{$color};background-color:{$skillname->competencycolour};";
        }
        $cells[] = clone($cell);
        $cells[] = $skillname->skillname;
        foreach ($selfscores as $selfscore) {
            $cells[] = isset($selfscore[$skillname->id]) ? $selfscore[$skillname->id] : 0;
        }
        $avgsum = 0;
        $avgcount = 0;
        foreach ($respondentscores as $respondentscoresbytype) {
            foreach ($respondentscoresbytype as $respondentscore) {
                $cells[] = isset($respondentscore->scores[$skillname->id]) ? $respondentscore->scores[$skillname->id] : 0;
                $avgsum += isset($respondentscore->scores[$skillname->id]) ? $respondentscore->scores[$skillname->id] : 0;
                $avgcount++;
            }
        }
        if ($showaverage) {
            $cells[] = sprintf('%01.2f', $avgsum/$avgcount);
        }
        $table->data[] = new html_table_row($cells);
    }

    if ($return) { return $table; }

    echo html_writer::table($table);
}

function get_score_table_header($text) {
    $heading = html_writer::start_span('rotated-text');
    $heading .= html_writer::start_span('rotated-text__inner');
    $heading .= $text;
    $heading .= html_writer::end_span();
    $heading .= html_writer::end_span();
    return $heading;
}

function get_aggregate_scores($threesixty, $skillnames, $selfscores, $respondentscores) {
    global $DB;

    if ($threesixty->skillgrade < 0){ // Custom scale
        $scale = $DB->get_record('scale', array('id' => -$threesixty->skillgrade));
        $scaleitems = explode(',', $scale->scale);
        $skillmaxscore = count($scaleitems) - 1; // Actual scores start at 0
    } else { // Numeric scale
        $skillmaxscore = $threesixty->skillgrade;
    }

    $competencies = array();

    while (!empty($skillnames)) {
        $skillname = array_shift($skillnames);
        if (!isset($competencies[$skillname->competencyid])) {
            $competencies[$skillname->competencyid] = new stdClass;
            $competencies[$skillname->competencyid]->name = $skillname->competencyname;
            $competencies[$skillname->competencyid]->colour = $skillname->competencycolour;
            $competencies[$skillname->competencyid]->maxscore = 0;
            $competencies[$skillname->competencyid]->respondentaverage = 0;
            $competencies[$skillname->competencyid]->scores = array();
        }

        $competencies[$skillname->competencyid]->maxscore += $skillmaxscore;

        $count = -1;
        $selftype = -1;
        foreach($selfscores as $type => $scores) {
            if ($type != $selftype) {
                $selftype = $type;
                $count++;
                if (!isset($competencies[$skillname->competencyid]->scores[$count])) {
                    $competencies[$skillname->competencyid]->scores[$count] = 0;
                }
            }
            $competencies[$skillname->competencyid]->scores[$count] += isset($scores[$skillname->id]) ? $scores[$skillname->id] : 0;
        }
        $respondentid = -1;
        $avgsum = 0;
        $avgcount = 0;
        foreach ($respondentscores as $respondentscoresbytype) {
            foreach ($respondentscoresbytype as $respondentscore) {
                if ($respondentscore->id != $respondentid) {
                    $respondentid = $respondentscore->id;
                    $count++;
                    if (!isset($competencies[$skillname->competencyid]->scores[$count])) {
                        $competencies[$skillname->competencyid]->scores[$count] = 0;
                    }
                }
                $competencies[$skillname->competencyid]->scores[$count] += isset($respondentscore->scores[$skillname->id]) ? $respondentscore->scores[$skillname->id] : 0;
                $avgsum += isset($respondentscore->scores[$skillname->id]) ? $respondentscore->scores[$skillname->id] : 0;
                $avgcount++;
            }
        }
        $competencies[$skillname->competencyid]->respondentaverage += $avgcount ? $avgsum/$avgcount : 0;
    }
    // Scale according to calculated max score and divisor
    foreach ($competencies as $competency) {
        $competency->respondentaverage = ($competency->respondentaverage / $competency->maxscore) * (100 / $threesixty->divisor);
        foreach ($competency->scores as $scoreindex => $score) {
            $competency->scores[$scoreindex] = ($score / $competency->maxscore) * (100 / $threesixty->divisor);
        }
        $competency->maxscore = (100 / $threesixty->divisor);
    }
    return $competencies;
}

function print_feedback_table($feedback, $curcompetency) {
    global $context;

    if (has_capability('mod/threesixty:feedbackview', $context)) {
        //Set up the column names.
        $header = array(get_string('feedbacks', 'threesixty'));
        $table = new html_table();
        $table->head = $header;
        $table->width = '100%';
        foreach ($feedback as $f) {
            if ($f->competencyid == $curcompetency) {
                $table->data[] = array("<span class='feedback'>".$f->feedback."</span>");
            }
        }
        if (!empty($table->data)) {
            echo html_writer::table($table);
        }
    }
}

function get_spiderweb_urls($cmid, $threesixty, $skillnames, $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage, $showtitle = true) {
    $showaverage = $showrespondentaverage && !empty($respondentscores);

    $spiderweburls = array();

    $competencies = get_aggregate_scores($threesixty, $skillnames, $selfscores, $respondentscores);

    $data = new stdClass();
    $data->cmid = $cmid;
    $data->settings = $threesixty->spiderwebsettings;
    $data->maxscore = 100 / $threesixty->divisor;
    $data->labels = array();
    $data->points = array();
    $data->title = get_string('allresults', 'threesixty');
    $data->showtitle = $showtitle;

    $count = 0;
    foreach (array_keys($selfscores) as $type) {
        $description = get_string('yourscore', 'threesixty');
        if (count($selfscores) > 1) {
            $description .= " [{$selftypes[$type]}]";
        }
        $data->points[$count] = new stdClass();
        $data->points[$count]->name = "series{$count}";
        $data->points[$count]->description = $description;
        $data->points[$count]->polygon = false;
        $count++;
    }

    // Separate views always contain self data
    $splitdatabase = clone($data);
    $splitdatabase->drawpolygon = true;
    $splitdatabase->drawinreverse = true;
    $splitdata = array();

    foreach ($respondentscores as $type => $respondentscoresbytype) {
        foreach ($respondentscoresbytype as $respondentscore) {
            $data->points[$count] = new stdClass();
            $data->points[$count]->name = "series{$count}";
            $data->points[$count]->description = $respondentscore->firstname.' '.$respondentscore->lastname;
            $splitdata[$count] = clone($splitdatabase);
            $splitdata[$count]->title = get_string('comparisonwith', 'threesixty', $respondentscore->firstname.' '.$respondentscore->lastname);
            $splitdata[$count]->points[$count] = new stdClass();
            $splitdata[$count]->points[$count]->name = "series{$count}";
            $splitdata[$count]->points[$count]->description = $respondentscore->firstname.' '.$respondentscore->lastname;
            $count++;
        }
    }
    if ($showaverage) {
        $data->points[$count] = new stdClass();
        $data->points[$count]->name = "series{$count}";
        $data->points[$count]->description = get_string('respondentaverage', 'threesixty');
        $average = clone($splitdatabase);
        $average->title = get_string('comparisonwith', 'threesixty', get_string('respondentaverage', 'threesixty'));
        $average->points[$count] = new stdClass();
        $average->points[$count]->name = "series{$count}";
        $average->points[$count]->description = get_string('respondentaverage', 'threesixty');
    }

    foreach ($competencies as $competency) {
        $data->labels[] = $competency->name;
        foreach ($splitdata as $splitsplitdata) {
            $splitsplitdata->labels[] = $competency->name;
        }
        $count = 0;
        foreach ($competency->scores as $score) {
            $data->points[$count]->scores[] = $score;
            if (isset($splitdata[$count])) {
                $splitdata[$count]->points[$count]->scores[] = $score;
            }
            $count++;
        }
        if ($showaverage) {
            $average->labels[] = $competency->name;
            $data->points[$count]->scores[] = $competency->respondentaverage;
            $average->points[$count]->scores[] = $competency->respondentaverage;
        }
    }
    $spiderdir = make_upload_directory('threesixty/spiderdata');
    $urldata = base64_encode(serialize($data));
    $filename = uniqid('', true);
    file_put_contents($spiderdir.'/'.$filename, $urldata);
    $spiderweburl = new stdClass();
    $spiderweburl->url = new moodle_url('/mod/threesixty/spiderweb.php', array('data' => $filename));
    $spiderweburl->title = $data->title;
    $spiderweburls[] = clone($spiderweburl);
    if ($showaverage) {
        $filename = uniqid('', true);
        $urldata = base64_encode(serialize($average));
        file_put_contents($spiderdir.'/'.$filename, $urldata);
        $spiderweburl->url = new moodle_url('/mod/threesixty/spiderweb.php', array('data' => $filename));
        $spiderweburl->title = $average->title;
        $spiderweburls[] = clone($spiderweburl);
    }
    foreach ($splitdata as $splitsplitdata) {
        $filename = uniqid('', true);
        $urldata = base64_encode(serialize($splitsplitdata));
        file_put_contents($spiderdir.'/'.$filename, $urldata);
        $spiderweburl->url = new moodle_url('/mod/threesixty/spiderweb.php', array('data' => $filename));
        $spiderweburl->title = $splitsplitdata->title;
        $spiderweburls[] = clone($spiderweburl);
    }

    return $spiderweburls;
}

function print_spiderweb($urldata, $context){
    global $CFG;

    $width = 1000;
    $height = $width * 1.05;

    $settings = !empty($urldata->settings) ? unserialize(base64_decode($urldata->settings)) : array();

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_threesixty', 'spiderbackground', 0, '', false);
    $file = reset($files);
    $background = false;
    if ($file && $file->is_valid_image()) {
        $background = imagecreatefromstring($file->get_content());
    }

    require_once("$CFG->dirroot/mod/threesixty/lib/pChart/class/pData.class.php");
    require_once("$CFG->dirroot/mod/threesixty/lib/pChart/class/pDraw.class.php");
    require_once("$CFG->dirroot/mod/threesixty/lib/pChart/class/pRadar.class.php");
    require_once("$CFG->dirroot/mod/threesixty/lib/pChart/class/pImage.class.php");

    /* Create and populate the pData object */
    $data = new pData();
    $data->loadPalette("$CFG->dirroot/mod/threesixty/lib/pChart/palettes/arup.color", true);
    foreach ($urldata->points as $point) {
        $data->addPoints($point->scores, $point->name);
        $data->setSerieDescription($point->name, $point->description);
        if (isset($point->polygon)) {
            $data->setSeriePolygon($point->name, $point->polygon);
        }
    }

    /* Define the absissa serie */
    $data->addPoints($urldata->labels, 'Labels');
    $data->setAbscissa('Labels');

    /* Create the pChart object */
    $image = new pImage($width, $height, $data);

    /* Draw a background */
    if ($background) {
        imagecopyresized($image->Picture, $background, 0, 0, 0, 0, $width, $height, imagesx($background), imagesy($background));
        imagedestroy($background);
    } else {
        $colour = array('R'=>255, 'G'=>255, 'B'=>255);
        $image->drawFilledRectangle(0, 0, $width, $height, $colour);
    }

    /* Add a border to the picture */
    $image->drawRectangle(0, 0, $width-1, $height-1, array('R' => 38, 'G' => 38, 'B' => 38));

    if ($urldata->showtitle) {
        /* Set the title font properties */
        $titlefont = isset($settings['titlefont']) ? $settings['titlefont'] : 'Arimo-Regular.ttf';
        $titlefontsize = isset($settings['titlefontsize']) ? $settings['titlefontsize'] : 16;
        list($titler, $titleg, $titleb) = isset($settings['titlecolour']) ? sscanf($settings['titlecolour'], "#%02x%02x%02x") : array(0, 0, 0);
        $image->setFontProperties(
            array(
                'FontName' => "$CFG->dirroot/mod/threesixty/lib/pChart/fonts/{$titlefont}",
                'FontSize' => $titlefontsize,
                'R' => $titler, 'G' => $titleg, 'B' => $titleb
            )
        );

        /* Write the title */
        $titlepos = $image->getTextBox(0, 0, $image->FontName, $image->FontSize, 0, $urldata->title);
        $titlewidth = abs($titlepos[1]['X'] - $titlepos[0]['X']);
        while ($titlewidth > $width - 20) {
            $image->setFontProperties(array('FontSize' => $image->FontSize - 1));
            $titlepos = $image->getTextBox(0, 0, $image->FontName, $image->FontSize, 0, $urldata->title);
            $titlewidth = abs($titlepos[1]['X'] - $titlepos[0]['X']);
        }
        $titleheight = abs($titlepos[0]['Y'] - $titlepos[3]['Y']);
        $image->drawText(round(($width - $titlewidth)/2), 20 + $titleheight, $urldata->title);
    }

    //  Get label settings
    $labelfont = isset($settings['labelfont']) ? $settings['labelfont'] : 'Arimo-Regular.ttf';
    $labelfontsize = isset($settings['labelfontsize']) ? $settings['labelfontsize'] : 12;
    list($labelr, $labelg, $labelb) = isset($settings['labelcolour']) ? sscanf($settings['labelcolour'], "#%02x%02x%02x") : array(0, 0, 0);
    $image->setFontProperties(
        array(
            'FontName' => "$CFG->dirroot/mod/threesixty/lib/pChart/fonts/{$labelfont}",
            'FontSize' => $labelfontsize,
            'R' => $labelr, 'G' => $labelg, 'B' => $labelb
        )
    );

    /* Create the pRadar object */
    $chart = new pRadar();

    /* Draw a radar chart */
    $image->setGraphArea(50, 75, 950, 950);

    $options = array(
        'Layout' => RADAR_LAYOUT_STAR,
        'LabelPos' => RADAR_LABELS_ROTATED,
        'AxisRotation' => -90,
        'FixedMax' => $urldata->maxscore,
        'Segments' => 5,
        'SegmentHeight' => $urldata->maxscore / 5,
        'AxisAlpha' => 100,
        'LabelsBGAlpha' => 100,
        'BackgroundAlpha' => 0, // Let it all through...
        'LabelPadding' => 6,
        'AxisBorderOffset' => 6,
        'PointRadius' => isset($settings['seriespointradius']) ? $settings['seriespointradius'] : 4,
        'LineWeight' => isset($settings['serieslineweight']) ? $settings['serieslineweight'] : 2,
        'PolyAlpha' => 20,
        'WriteLabels' => isset($settings['labelshow']) ? $settings['labelshow'] : true,
    );

    list($axisr, $axisg, $axisb) = isset($settings['axiscolour']) ? sscanf($settings['axiscolour'], "#%02x%02x%02x") : array(0, 0, 0);
    $axisfont = isset($settings['axisfont']) ? $settings['axisfont'] : 'Arimo-Regular.ttf';
    $options['AxisFontName'] = "$CFG->dirroot/mod/threesixty/lib/pChart/fonts/{$axisfont}";
    $options['AxisFontSize'] = isset($settings['axisfontsize']) ? $settings['axisfontsize'] : 12;
    $options['AxisFontColorR'] = $axisr;
    $options['AxisFontColorG'] = $axisg;
    $options['AxisFontColorB'] = $axisb;
    $options['AxisFontColorA'] = 100;

    if (!empty($urldata->drawpolygon)) {
        $options['DrawPoly'] = true;
    }

    if (!empty($urldata->drawinreverse)) {
        $options['DrawInReverse'] = true;
    }

    $chart->drawRadar($image, $data, $options);

    /* Write the chart legend */
    $legendfont = isset($settings['legendfont']) ? $settings['legendfont'] : 'Arimo-Regular.ttf';
    $legendfontsize = isset($settings['legendfontsize']) ? $settings['legendfontsize'] : 11;
    list($legendr, $legendg, $legendb) = isset($settings['legendcolour']) ? sscanf($settings['legendcolour'], "#%02x%02x%02x") : array(0, 0, 0);
    $image->setFontProperties(
        array(
            'FontName' => "$CFG->dirroot/mod/threesixty/lib/pChart/fonts/{$legendfont}",
            'FontSize' => $legendfontsize,
            'R' => $legendr, 'G' => $legendg, 'B' => $legendb
        )
    );
    $legendboxsize = isset($settings['legendboxsize']) ? $settings['legendboxsize'] : 10;
    $image->drawLegend(20, 1040 - $image->FontSize, array('BoxWidth' => $legendboxsize, 'BoxHeight' => $legendboxsize, 'Style' => LEGEND_NOBORDER, 'Mode' => LEGEND_HORIZONTAL, 'ModeDir' => LEGEND_HORIZONTAL_FLIPPED, 'XSpacing' => 10));

    /* Render the picture */
    //$image->stroke();
    imagepng($image->Picture);
}
