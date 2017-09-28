<?php

use block_certification\certification;

/**
 * certification block renderer
 *
 * @package    block_certification
 */

defined('MOODLE_INTERNAL') || die;

class block_certification_renderer extends plugin_renderer_base {

    /**
     * Prepare table with current user certifications details
     * 
     * @param $certifications
     * @return string
     */
    public static function show_certifications($certifications, $view){
        global $OUTPUT;

        $type = !empty($view->type) ? $view->type : null;
        
        $output = '';
        if (count($certifications) == 0) {
            $output .= get_string('nocertificationsassigned', 'block_certification');
        } else {
            $currentcategory = 0;
            $prevcertification = 0;
            $currentcohort = 0;
            $headerhtml = html_writer::start_tag('tr', ['class' => 'category-header']);
            $headerhtml .= html_writer::tag('td', get_string('certificationname', 'block_certification'));
            if ($type !== 'cohort') {
                $headerhtml .= html_writer::tag('td', get_string('certificationrole', 'block_certification'));
            }
            $headerhtml .= html_writer::tag('td', get_string('certificationlastcompletion', 'block_certification'));
            $headerhtml .= html_writer::tag('td', get_string('certificationeenewaldate', 'block_certification'));
            $headerhtml .= html_writer::end_tag('tr');

            $output .= html_writer::start_tag('table', ['class' => 'certification-table']);
            $statusnote = false;
            // Need to reset cohort array first time round.
            $resetcohorts = true;
            foreach ($certifications as $certification) {
                if ($type !== 'cohort') {
                    if ($resetcohorts) {
                        $cohorts = [];
                        $resetcohorts = false;
                    }
                    if (!empty($certification->cohortid)) {
                        $cohorts[] = (!empty($certification->cohortidnumber) ? $certification->cohortidnumber : $certification->cohortname);
                    }
                    // Let's grab the next entry in the array.
                    // This will jump on but not affect the foreach loop.
                    $next = next($certifications);
                    // Are we on the next certification and looking at another row of info for the same certification coming next?
                    if ($certification->certifid !== $prevcertification && $next !== false && $next->certifid === $certification->certifid) {
                        // If the next entry exists and is for the same certification skip this loop.
                        // This enables us to continue to build the cohort array.
                        continue;
                    }
                    // Only reset this once we know a new certification is coming next and we've stopped looping through getting cohorts.
                    $prevcertification = $certification->certifid;
                    $resetcohorts = true;
                }

                $needsheader = ($type !== 'cohort' && $certification->categoryid !== $currentcategory)
                        || ($type === 'cohort' && $certification->cohortid !== $currentcohort);
                if ($needsheader) {
                    $currentcategory = $certification->categoryid;
                    $currentcohort = $certification->cohortid;
                    $output .= html_writer::start_tag('tr',['class' => 'certification-category']);
                    $colspan = ($type !== 'cohort' ? 3 : 2);
                    if ($type !== 'cohort') {
                        $name = $certification->categoryname;
                    } else {
                        $name = (!empty($certification->cohortidnumber) ? $certification->cohortidnumber : $certification->cohortname);
                    }
                    $output .= html_writer::tag('td', $name, ['colspan' => $colspan]);
                    $progress = ($type !== 'cohort' ? $certification->categoryprogress : $certification->cohortprogress);
                    $rag = ($type !== 'cohort' ? $certification->categoryragstatus : $certification->cohortragstatus);
                    $progress = html_writer::tag('span', $progress.'%', ['class' => 'status-'.$rag]);
                    $output .= html_writer::tag('td', $progress, ['class' => 'category-progress']);
                    $output .= html_writer::end_tag('tr');
                    $output .= $headerhtml;
                    $categorystatusnote = ($certification->categoryprogress == 100 && $certification->categoryragstatus != \local_custom_certification\completion::RAG_STATUS_GREEN);
                    $cohortstatusnote = ($certification->cohortprogress == 100 && $certification->cohortragstatus != \local_custom_certification\completion::RAG_STATUS_GREEN);
                    if ($categorystatusnote || $cohortstatusnote) {
                        $statusnote = true;
                    }
                }

                if ($certification->progress == 100 && $certification->ragstatus != \local_custom_certification\completion::RAG_STATUS_GREEN) {
                    $statusnote = true;
                }

                $status = '';
                $optionalorexempt = ($certification->optional || $certification->exempt);
                if ($optionalorexempt && $certification->ragstatus == \local_custom_certification\completion::RAG_STATUS_GREEN) {
                    $status = html_writer::img($OUTPUT->pix_url('/i/checked'), '', ['class' => 'status']);
                } else if ($optionalorexempt && $certification->ragstatus == \local_custom_certification\completion::RAG_STATUS_AMBER) {
                    $status = html_writer::img($OUTPUT->pix_url('/i/caution'), '', ['class' => 'status']);
                } else {
                    $status =  html_writer::tag('span', '', ['class' => 'status status-'.$certification->ragstatus]);
                }

                $rowragclass = '';
                if ($certification->ragstatus === \local_custom_certification\completion::RAG_STATUS_RED) {
                    $rowragclass = ' certification-row-red';
                }
                $output .= html_writer::start_tag('tr',['class' => "certification-row{$rowragclass}"]);

                $certificationlink = html_writer::link(
                        new moodle_url('/local/custom_certification/overview.php', ['id' => $certification->certifid]),
                        $certification->certificationname);
                $output .= html_writer::tag('td', $status . $certificationlink);

                // Role(s).
                if ($type !== 'cohort') {
                    $output .= html_writer::tag('td', implode(html_writer::empty_tag('br'), $cohorts));
                }
                if ($certification->timecompleted > 0) {
                    $output .= html_writer::tag('td', userdate($certification->timecompleted, '%d %b %Y'));
                } else {
                    $output .= html_writer::tag('td', $certification->lasttimecompleted > 0 ? userdate($certification->lasttimecompleted, '%d %b %Y') : get_string('notcompleted', 'block_certification'));
                }
                if ($certification->exempt) {
                    $exempttitle = $certification->exemptionreason;
                    if ($certification->exemptionexpiry) {
                        $exempttitle .= html_writer::empty_tag('br');
                        $exempttitle .= get_string('exemptionexpires', 'block_certification');
                        $exempttitle .= '&nbsp;';
                        // Saved use strtotime which uses default timezone.
                        $exempttitle .= userdate($certification->exemptionexpiry, get_string('strftimedate'), date_default_timezone_get());
                    }
                    $exemptoutput = get_string('exempt', 'block_certification');
                    $exemptoutput .= '&nbsp;';
                    $exemptimg = html_writer::img($OUTPUT->pix_url('help'), get_string('helpwiththis'));
                    $exemptoutput .= html_writer::span($exemptimg, 'exempt-help', ['data-toggle' => 'tooltip', 'data-html' => true, 'title' => $exempttitle]);
                    $output .= html_writer::tag('td', $exemptoutput);
                } else if ($certification->renewaldate > 0 && $certification->renewaldate < time()) {
                    $output .= html_writer::tag('td', get_string('overdue', 'block_certification'));
                } else if ($certification->renewaldate > 0) {
                    $output .= html_writer::tag('td', userdate($certification->renewaldate, '%d %b %Y'));
                } else {
                    $output .= html_writer::tag('td', get_string('duedatenotset', 'block_certification'));
                }
                $output .= html_writer::end_tag('tr');
            }

            $output .= html_writer::end_tag('table');

            if ($statusnote) {
                $output .= html_writer::div(get_string('statusnote', 'local_custom_certification'), 'learner-status-note');
            }
            if (!empty($view->url)) {
                $switchtype = get_string(($type !== 'cohort' ? 'cohort' : 'category'), 'block_certification');
                $switchlink = html_writer::link($view->url, get_string('switchtoview', 'block_certification', $switchtype));
                $output .= html_writer::div($switchlink, 'view-link');
            }
        }
        return $output;
    }
}
