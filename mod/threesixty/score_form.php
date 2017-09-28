<?php

require_once $CFG->dirroot.'/lib/formslib.php';

class mod_threesixty_paginated_score_form extends moodleform {

    protected $_threesixty;
    protected $_radiogroups = array();

    function definition() {
        global $DB, $USER;

        $mform =& $this->_form;
        $a = $this->_customdata['a'];
        $this->_threesixty = $DB->get_record('threesixty', array('id' => $a));
        $competency = $this->_customdata['competency'];
        $page = $this->_customdata['page'];
        $nbpages = $this->_customdata['nbpages'];

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);

        $mform->addElement('hidden', 'code', $this->_customdata['code']);
        $mform->setType('code', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'page', $this->_customdata['page']);
        $mform->setType('page', PARAM_INT);

        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'typeid', $this->_customdata['typeid']);
        $mform->setType('typeid', PARAM_INT);

        if ($this->_threesixty->competencylabels) {
            $mform->addElement('header', 'competency', format_string($competency->name));
            $mform->addElement('html', '<div class="competencydescription">'.format_text($competency->description).'</div>');
        }

        if ($this->_threesixty->skillgrade < 0){
            // This is a non numeric scale, based on a custom scale
            $mform->addElement('html', '<div class="completionlegend"><p class="legendheading">'.get_string('legend:heading', 'threesixty').'</p><ul>');
            $scale = $DB->get_record('scale', array('id' => -$this->_threesixty->skillgrade));
            $scaleitems = explode(',', $scale->scale);
            $maxscale = count($scaleitems);
            $i = 0;
            foreach($scaleitems as $sci){
                $mform->addElement('html', "<li><b>$i</b> : $sci </li>");
                $i++;
            }
            $mform->addElement('html', '</ul></div>');
        } else {
            // This is a numeric scale
            $maxscale = $this->_threesixty->skillgrade + 1;
        }

        if ($competency->skills and count($competency->skills) > 0) {
            $mform->addElement('html','<div class="compheader">');

            $mform->addElement('header');
            $mform->addElement('html', '<table class="generaltable threesixty-score-table" width="100%"><tr>');
            $mform->addElement('html', '<th class="header text-left">'.threesixty_get_alternative_word($this->_threesixty, 'skill', 'skills').'</th><th class="header">'.get_string('notapplicable', 'threesixty').'</th>');
            for($i = 1 ; $i < $maxscale ; $i++){
                $mform->addElement('html', '<th class="header">'.$i.'</th>');
            }
            $mform->addElement('html', '</tr>');
            foreach ($competency->skills as $skill) {
                if ($USER->id != $this->_customdata['userid'] && !empty($skill->altname)) {
                    $skillname = format_string($skill->altname);
                } else {
                    $skillname = format_string($skill->name);
                }
                if ($USER->id != $this->_customdata['userid'] && !empty($skill->altdescription)) {
                    $skillname .= html_writer::empty_tag('br') . format_string($skill->altdescription);
                } elseif (!empty($skill->description)) {
                    $skillname .= html_writer::empty_tag('br') . format_string($skill->description);
                }
                $elementname = "score_{$skill->id}";
                $rowid = "score-{$skill->id}";
                $mform->addElement('html', "<tr id=\"{$rowid}\"><td class=\"cell c0\">$skillname</td>");
                $attribs = array();
                if ($competency->locked) {
                    $attribs['disabled'] = 'disabled';
                }
                for($i = 0 ; $i < $maxscale ; $i++){
                    $mform->addElement('html', "<td class=\"cell threesixty-score-cell\">");
                    $mform->addElement('radio', $elementname, '', '', $i, $attribs);
                    $mform->addElement('html', "</td>");
                }
                $mform->addElement('html', "</tr>");
                $mform->setDefault($elementname, -1); // Non-existent default to clear all buttons
                if (!$competency->locked) {
                    $this->_radiogroups[$skill->id] = true;
                }
            }
            $mform->addElement('html', '</table></div>');

        } else {
            $a = new stdClass();
            $a->skills = core_text::strtolower(threesixty_get_alternative_word($this->_threesixty, 'skill', 'skills'));
            $a->competency = core_text::strtolower(threesixty_get_alternative_word($this->_threesixty, 'competency'));
            $mform->addElement('html', get_string('noskills', 'threesixty', $a));
        }

        if ($competency->showfeedback == 1) {
            $mform->addElement('textarea', 'feedback', get_string('feedback'), array('cols'=>'53', 'rows'=>'8'));
            if ($competency->locked) {
                $mform->hardFreeze('feedback');
            }
        }

        // Paging buttons
        $buttonarray = array();
        if ($page > 1) {
            $buttonarray[] = &$mform->createElement('submit', 'previous', get_string('previous'));
        } else {
            $buttonarray[] = &$mform->createElement('submit', 'previous', get_string('previous'), array('disabled'=>true));
        }
        if ($page < $nbpages) {
            $buttonarray[] = &$mform->createElement('submit', 'next', get_string('next'));
        } else {
            $attrs = array();
            if ($competency->locked) {
                $buttonlabel = get_string('closebutton', 'threesixty');
            } else {
                $buttonlabel = get_string('finishbutton', 'threesixty');
                $attrs['class'] = 'btn-primary';
            }
            $buttonarray[] = &$mform->createElement('submit', 'finish', $buttonlabel, $attrs);
        }

        $a = new stdClass();
        $a->page = $page;
        $a->nbpages = $nbpages;

        $mform->addGroup($buttonarray, 'buttonarray', '', ' ' . get_string('xofy', 'threesixty', $a) . ' ');
        $mform->closeHeaderBefore('buttonarray');
    }

    public function validation($data, $files) {
        global $CFG;

        $mform =& $this->_form;

        $errors = parent::validation($data, $files);
        $styles = "\n<style>";
        foreach($this->_radiogroups as $skillid => $value) {
            if (!isset($data["score_{$skillid}"])) {
                $errors['radiogroup'] = true;
                $styles .= "\ntr#score-{$skillid} .threesixty-score-cell {background-color: #f2dede;}";
            }
        }
        $styles .= "\n</style>";

        if (isset($errors['radiogroup'])) {
            if (!isset($CFG->additionalhtmlhead)) {
                $CFG->additionalhtmlhead = '';
            }
            $CFG->additionalhtmlhead .= $styles;

            $a = core_text::strtolower(threesixty_get_alternative_word($this->_threesixty, 'skill', 'skills'));
            $radiogrouperrors =& $mform->createElement('html', html_writer::tag('div', get_string('missingscores', 'threesixty', $a), array('class' => 'alert fade in alert-danger')));

            $mform->insertElementBefore( $radiogrouperrors, 'a');

        }

        return $errors;
    }
}

class mod_threesixty_single_page_score_form extends moodleform {

    protected $_threesixty;
    protected $_radiogroups = array();

    function definition() {
        global $DB, $USER;

        $mform =& $this->_form;
        $a = $this->_customdata['a'];
        $this->_threesixty = $DB->get_record('threesixty', array('id' => $a));
        $competencies = $this->_customdata['competenciescopy'];
        $locked = $this->_customdata['locked'];
        $questionorder = $this->_customdata['questionorder'];

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);

        $mform->addElement('hidden', 'code', $this->_customdata['code']);
        $mform->setType('code', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'typeid', $this->_customdata['typeid']);
        $mform->setType('typeid', PARAM_INT);

        if ($this->_threesixty->skillgrade < 0){
            // This is a non numeric scale, based on a custom scale
            $mform->addElement('html', '<div class="completionlegend"><p class="legendheading">'.get_string('legend:heading', 'threesixty').'</p><ul>');
            $scale = $DB->get_record('scale', array('id' => -$this->_threesixty->skillgrade));
            $scaleitems = explode(',', $scale->scale);
            $maxscale = count($scaleitems);
            $i = 0;
            foreach($scaleitems as $sci){
                $mform->addElement('html', "<li><b>$i</b> : $sci </li>");
                $i++;
            }
            $mform->addElement('html', '</ul></div>');
        } else {
            // This is a numeric scale
            $maxscale = $this->_threesixty->skillgrade + 1;
        }

        $mform->addElement('header');
        $mform->addElement('html', '<table class="generaltable threesixty-score-table" width="100%"><tr>');
        $mform->addElement('html', '<th class="header">&nbsp;</th><th class="header text-left">'.threesixty_get_alternative_word($this->_threesixty, 'skill', 'skills').'</th><th class="header">'.get_string('notapplicable', 'threesixty').'</th>');
        for($i = 1 ; $i < $maxscale ; $i++){
            $mform->addElement('html', '<th class="header">'.$i.'</th>');
        }
        $mform->addElement('html', '</tr>');
        $rowcount = 0;
        while (!empty($competencies)) {
            $rowcount++;
            // grab a competency and a skill (depending on questionorder
            if ($questionorder == 'random') {
                $competencykey = array_rand($competencies);
                $competency = $competencies[$competencykey];
                $skillkey = array_rand($competencies[$competencykey]->skills);
                $skill = $competencies[$competencykey]->skills[$skillkey];
                // Tidy up
                unset($competencies[$competencykey]->skills[$skillkey]);
                if (empty($competencies[$competencykey]->skills)) {
                    unset($competencies[$competencykey]);
                }
            } else {
                $competency = array_shift($competencies);
                $skill = array_shift($competency->skills);
                if (!empty($competency->skills)) {
                    array_push($competencies, $competency);
                }
            }

            $skillname = '';
            if ($this->_threesixty->competencylabels) {
                $skillname .= format_string($competency->name) . html_writer::empty_tag('br');
            }
            if ($USER->id != $this->_customdata['userid'] && !empty($skill->altname)) {
                $skillname .= format_string($skill->altname);
            } else {
                $skillname .= format_string($skill->name);
            }
            if ($USER->id != $this->_customdata['userid'] && !empty($skill->altdescription)) {
                $skillname .= html_writer::empty_tag('br') . format_string($skill->altdescription);
            } elseif (!empty($skill->description)) {
                $skillname .= html_writer::empty_tag('br') . format_string($skill->description);
            }

            $elementname = "score[{$competency->id}][{$skill->id}]";
            $rowid = "score-{$competency->id}-{$skill->id}";
            $mform->addElement('html', "<tr id=\"{$rowid}\"><td>$rowcount</td><td class=\"cell c0\">$skillname</td>");
            $attribs = array();
            if ($competency->locked) {
                $attribs['disabled'] = 'disabled';
            }
            for($i = 0 ; $i < $maxscale ; $i++){
                $mform->addElement('html', "<td class=\"cell threesixty-score-cell\">");
                $mform->addElement('radio', $elementname, '', '', $i, $attribs);
                $mform->addElement('html', "</td>");
            }
            $mform->addElement('html', "</tr>");
            $mform->setDefault($elementname, -1); // Non-existent default to clear all buttons

            if (!$competency->locked) {
                $this->_radiogroups[$competency->id][$skill->id] = true;
            }
        }
        $mform->addElement('html', '</table>');

        // @TODO : Feedback (how to show on single page?)

        $buttonarray = array();
        $attrs = array();
        if ($locked) {
            $buttonlabel = get_string('closebutton', 'threesixty');
            $attrs['class'] = 'btn-default';
        } else {
            $buttonlabel = get_string('finishbutton', 'threesixty');
            $attrs['class'] = 'btn-primary';
        }
        $buttonarray[] = &$mform->createElement('submit', 'finish', $buttonlabel, $attrs);
        $mform->addGroup($buttonarray, 'buttonarray');
        $mform->closeHeaderBefore('buttonarray');
    }

    public function validation($data, $files) {
        global $CFG;

        $mform =& $this->_form;

        $errors = parent::validation($data, $files);
        $styles = "\n<style>";
        foreach($this->_radiogroups as $competencyid => $skills) {
            foreach($skills as $skillid => $value) {
                $rowid = "score-{$competencyid}-{$skillid}";
                if (!isset($data['score'][$competencyid][$skillid])) {
                    $errors['radiogroup'] = true;
                    $styles .= "\ntr#{$rowid} .threesixty-score-cell {background-color: #f2dede;}";
                }
            }
        }
        $styles .= "\n</style>";

        if (isset($errors['radiogroup'])) {
            if (!isset($CFG->additionalhtmlhead)) {
                $CFG->additionalhtmlhead = '';
            }
            $CFG->additionalhtmlhead .= $styles;

            $a = core_text::strtolower(threesixty_get_alternative_word($this->_threesixty, 'skill', 'skills'));
            $radiogrouperrors =& $mform->createElement('html', html_writer::tag('div', get_string('missingscores', 'threesixty', $a), array('class' => 'alert fade in alert-danger')));

            $mform->insertElementBefore( $radiogrouperrors, 'a');

        }

        return $errors;
    }
}
