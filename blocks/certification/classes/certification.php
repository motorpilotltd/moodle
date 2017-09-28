<?php

namespace block_certification;
use local_custom_certification\completion;

/**
 * @package block_certification
 */

class certification {
    /**
     * Get all visible certifications with details for current user
     * @return array
     */
    public static function get_data($type = null) {
        global $DB, $USER;

        if ($type === 'cohort') {
            $orderby = "(CASE
                    WHEN co.name IS NULL THEN 1
                    ELSE 0
                END),
                co.name ASC, c.fullname ASC, c.id ASC";
        } else {
            $orderby = "cc.sortorder ASC, c.fullname ASC, c.id ASC,
                (CASE
                    WHEN co.name IS NULL THEN 1
                    ELSE 0
                END),
                co.name ASC";
        }

        $query = "
            SELECT
                cau.id as id,
                cua.duedate,
                cua.optional,
                c.id as certifid,
                c.fullname as certificationname,
                cc.name as categoryname,
                cc.id as categoryid,
                comp.duedate as renewaldate,
                comp.status,
                comp.certifpath,
                comp.timecompleted,
                cca.timecompleted as lasttimecompleted,
                cca.timeexpires as lasttimeexpires,
                co.id as cohortid,
                co.name as cohortname,
                co.idnumber as cohortidnumber,
                ce.id as exempt,
                ce.reason as exemptionreason,
                ce.timeexpires as exemptionexpiry
            FROM
                {certif_assignments_users} cau
            JOIN
                {certif_user_assignments} cua
                ON cua.certifid = cau.certifid AND cua.userid = cau.userid
            JOIN
                {certif} c
                ON c.id = cua.certifid
            JOIN
                {course_categories} cc
                ON cc.id = c.category
            JOIN
                {certif_completions} comp
                ON comp.certifid = c.id AND comp.userid = cua.userid
            LEFT JOIN
                (
                    SELECT
                        cca.userid,
                        cca.certifid,
                        MAX(cca.timecompleted) as timecompleted,
                        MAX(cca.timeexpires) as timeexpires
                    FROM
                        {certif_completions_archive} cca
                    GROUP BY
                        cca.userid, cca.certifid
                ) as cca
                ON cca.userid = cua.userid AND cca.certifid = c.id
            LEFT JOIN
                {certif_assignments} ca
                ON ca.id = cau.assignmentid AND ca.assignmenttype = :assignmenttype
            LEFT JOIN
                {cohort} co
                ON co.id = ca.assignmenttypeid
            LEFT JOIN
                {certif_exemptions} ce
                ON ce.userid = cau.userid AND ce.certifid = cau.certifid AND ce.archived = :archived AND (ce.timeexpires = 0 OR ce.timeexpires > :now)
            WHERE
                cau.userid = :userid
                AND c.deleted = :deleted
                AND c.visible = :visible
                AND c.uservisible = :uservisible
            ORDER BY
                {$orderby}
        ";

        $params = [
            'assignmenttype' => \local_custom_certification\certification::ASSIGNMENT_TYPE_AUDIENCE,
            'userid' => $USER->id,
            'deleted' => 0,
            'visible' => 1,
            'archived' => 0,
            'now' => time(),
            'uservisible' => 1
        ];

        $certifications = $DB->get_records_sql($query, $params);

        $categoryprogress = [];
        $categoryragstatus = [];
        $cohortprogress = [];
        $cohortragstatus = [];
        foreach($certifications as $certification){
            /**
             * Get progress
             */
            $certifprogress = completion::get_user_progress($certification->certifid, $USER->id);
            if ($certification->lasttimeexpires > time()) {
                // Previous certification completion has not yet expired.
                $certification->progress = 100;
            } else if ($certification->certifpath == \local_custom_certification\certification::CERTIFICATIONPATH_RECERTIFICATION){
                $certification->progress = $certifprogress['recertification'];
            }else{
                $certification->progress = $certifprogress['certification'];
            }

            /**
             * Get RAG status
             */
            $optional = $certification->optional || $certification->exempt;
            $certification->ragstatus = completion::get_rag_status($certification->timecompleted, $certification->renewaldate, $optional);
            if(!isset($categoryprogress[$certification->categoryid])){
                $categoryprogress[$certification->categoryid] = [];
                $categoryragstatus[$certification->categoryid] = completion::RAG_STATUS_GREEN;
            }
            if(!isset($cohortprogress[$certification->cohortid])){
                $cohortprogress[$certification->cohortid] = [];
                $cohortragstatus[$certification->cohortid] = completion::RAG_STATUS_GREEN;
            }
            if($optional === false){
                // Categories.
                $categoryprogress[$certification->categoryid][] = ($certification->progress == 100 ? 100 : 0);
                if(($categoryragstatus[$certification->categoryid] == completion::RAG_STATUS_GREEN && $certification->ragstatus != completion::RAG_STATUS_GREEN)
                    || ($categoryragstatus[$certification->categoryid] == completion::RAG_STATUS_AMBER && $certification->ragstatus != completion::RAG_STATUS_RED)){
                    $categoryragstatus[$certification->categoryid] = $certification->ragstatus;
                }
                // Cohorts.
                $cohortprogress[$certification->cohortid][] = ($certification->progress == 100 ? 100 : 0);
                if(($cohortragstatus[$certification->cohortid] == completion::RAG_STATUS_GREEN && $certification->ragstatus != completion::RAG_STATUS_GREEN)
                    || ($cohortragstatus[$certification->cohortid] == completion::RAG_STATUS_AMBER && $certification->ragstatus != completion::RAG_STATUS_RED)){
                    $cohortragstatus[$certification->cohortid] = $certification->ragstatus;
                }
            }
        }

        foreach($categoryprogress as $categoryid => $progress){
            if (empty($progress)) {
                // All optional/exempt.
                $categoryprogress[$categoryid] = 100;
                $categoryragstatus[$categoryid] = completion::RAG_STATUS_OPTIONAL;
            } else {
                $categoryprogress[$categoryid] = round(array_sum($progress) / max(1, count($progress)));
            }
        }
        foreach($cohortprogress as $cohortid => $progress){
            if (empty($progress)) {
                // All optional/exempt.
                $cohortprogress[$cohortid] = 100;
                $cohortragstatus[$cohortid] = completion::RAG_STATUS_OPTIONAL;
            } else {
                $cohortprogress[$cohortid] = round(array_sum($progress) / max(1, count($progress)));
            }
        }
        foreach($certifications as $certification){
            // Categories.
            $certification->categoryprogress = $categoryprogress[$certification->categoryid];
            $certification->categoryragstatus = $categoryragstatus[$certification->categoryid];
            // Cohorts.
            $certification->cohortprogress = $cohortprogress[$certification->cohortid];
            $certification->cohortragstatus = $cohortragstatus[$certification->cohortid];
        }

        return $certifications;
    }
}
