<?php

namespace wa_learning_path\lib;

/**
 * Convert date to unix timestamp
 * @param $year
 * @param $month
 * @param $date
 * @return int
 */
function time($year, $month, $date) {
	// Get the calendar type used - see MDL-18375.
	$calendartype = \core_calendar\type_factory::get_calendar_instance();
	$gregoriandate = $calendartype->convert_to_gregorian($year, $month, $date);

	return make_timestamp($gregoriandate['year'], $gregoriandate['month'], $gregoriandate['day'], 0, 0, 0, 99, true);
}

/**
 * Include model class.
 */
function load_model($name = null) {
	global $CFG;

	if (!isset($name)) {
		$name = 'main';
	}

	// Autoloading model class.
	$model = $CFG->dirroot . '/local/wa_learning_path/' . 'model' . DIRECTORY_SEPARATOR . $name . '.class.php';

	if (file_exists($model)) {
		require_once($model);
		return true;
	}

	throw new \Exception('Model ' . $name . ' does not exists');
}

/**
 * Include form class.
 */
function load_form($name = null) {
	global $CFG;
	if (!isset($name)) {
		$name = 'main';
	}

	// Autoloading model class.
	$form = $CFG->dirroot . '/local/wa_learning_path/' . 'form' . DIRECTORY_SEPARATOR . $name . '.class.php';
	if (file_exists($form)) {
		require_once($form);
		return true;
	}

	throw new \Exception('Form ' . $form . ' for ' . $name . " does not found");
}

function get_custom_field($shortname) {
	global $DB;
	$r = $DB->get_record_sql("select id from {coursemetadata_info_field} where shortname = '" . $shortname . "'");

	return empty($r) ? 0 : $r->id;
}

/**
 * Create custom profile field.
 * @param $shortname
 * @param string $name
 * @param int $categoryid
 * @param string $description
 * @param string $datatype
 * @param string $defaultdata
 * @param string $param1
 * @param string $param2
 * @param string $param3
 * @param string $param4
 * @param string $param5
 * @return bool|int
 */
function create_profile_field($shortname, $name = '', $categoryid = 0, $description = '', $datatype = 'text', $defaultdata = '', $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '') {
	global $DB;

	// Check if field exists already.
	$field = $DB->get_record('user_info_field', array('shortname' => $shortname));
	if (empty($field)) {
		// Check categories.
		if (!$categoryid) {
			$categories = $DB->get_records('user_info_category', array('id' => 1), 'sortorder ASC');

			if (empty($categories)) {
				// Create a new category if there is no categories at all.
				$cat = new \stdClass();
				$cat->name = 'Other fields';
				$categoryid = $DB->insert_record('user_info_category', $cat);
			} else {
				$categoryid = reset($categories)->id;
			}
		}

		$field = array();
		$field['shortname'] = $shortname;
		$field['name'] = $name ? $name : $shortname;
		$field['datatype'] = $datatype;
		$field['categoryid'] = $categoryid;
		$field['description'] = $description;
		$field['descriptionformat'] = FORMAT_HTML;
		$field['defaultdata'] = $defaultdata;
		$field['param1'] = $param1;
		$field['param2'] = $param2;
		$field['param3'] = $param3;
		$field['param4'] = $param4;
		$field['param5'] = $param5;
		$field['defaultdataformat'] = FORMAT_HTML;

		// Convert to object.
		$field = (object) $field;

		$lastinsertid = $DB->insert_record('user_info_field', $field);
		$field->id = $lastinsertid;
		return $field;
	} else {
		// Return existing field id.
		return $field;
	}
}

function is_ajax() {
	$ajax = optional_param('ajax', 0, PARAM_INT);
	return strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest' || $ajax;
}

/**
 * Content editor is (this is my assumption) user who has at least one of these capabilities
 * You should only be able to access the learning path management if you have one of the following capabilities:
	local/wa_learning_path:addlearningpath
	local/wa_learning_path:amendlearningcontent
	local/wa_learning_path:deletelearningpath
	local/wa_learning_path:editlearningmatrix
	local/wa_learning_path:editmatrixgrid
	local/wa_learning_path:publishlearningpath
 * @param bool $userid
 * @return bool
 * @throws \coding_exception
 * @throws \dml_exception
 */
function is_contenteditor($userid = false) {

	return
		\wa_learning_path\lib\has_capability('addlearningpath') ||
		\wa_learning_path\lib\has_capability('amendlearningcontent') ||
		\wa_learning_path\lib\has_capability('deletelearningpath') ||
		\wa_learning_path\lib\has_capability('editlearningmatrix') ||
		\wa_learning_path\lib\has_capability('editmatrixgrid') ||
		\wa_learning_path\lib\has_capability('publishlearningpath');
}

/**
 * Activity editor is (this is my assumption) user who has at least one of these capabilities
 * @param bool $userid
 * @return bool
 * @throws \coding_exception
 * @throws \dml_exception
 */
function is_activity_editor($userid = false) {

	return \wa_learning_path\lib\has_capability('editactivity') ||
		\wa_learning_path\lib\has_capability('addactivity') ||
		\wa_learning_path\lib\has_capability('deleteactivity');
}

function get_modules($ids = array(), $matrixview = false, $group_courses = true) {
	global $DB, $USER;

	//$fieldlevel = \wa_learning_path\lib\get_custom_field(WA_LEARNING_PATH_CUSTOM_FIELD_LEVEL);
	$field702010 = \wa_learning_path\lib\get_custom_field(WA_LEARNING_PATH_CUSTOM_FIELD_702010);
	$fieldmethodology = \wa_learning_path\lib\get_custom_field(WA_LEARNING_PATH_CUSTOM_FIELD_METHODOLOGY);

	if ($ids) {
		list($usql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'id');
		$usql = ' and c.id ' . $usql;
	} else {
		$params = array();
		$usql = '';
	}

	$completionsql = "";
	$join = "";
	if ($matrixview) {
		$completionsql = ", cc.timestarted ";
		$join = " LEFT JOIN {course_completions} cc ON cc.course = c.id AND cc.userid = :cc_userid ";
		$params['cc_userid'] = (int) $USER->id;
	}

	$unique = $DB->sql_concat(('c.id'), "'-'", ('rc.regionid'));

	$sql = "select CASE WHEN rc.regionid IS NULL THEN " . $DB->sql_concat(('c.id'), "'-'", "'no_region'") . "
                    ELSE {$unique} END as unique_id, c.id, c.fullname, c.summary, c.summaryformat, r.id as regionid, r.name as region {$completionsql} "
		//. (!empty($fieldlevel) ? ", d1.data as level ": " ")
		. (!empty($field702010) ? ", d2.data as p702010 " : " ")
		. (!empty($fieldmethodology) ? ", d3.data as methodology " : " ")
		. " from {course} c " .
		" left join {local_regions_reg_cou} rc on rc.courseid = c.id " .
		$join .
		" left join {local_regions_reg} r on rc.regionid = r.id " .
		//($fieldlevel ? " left join {coursemetadata_info_data} d1 on d1.course = c.id and d1.fieldid = ".$fieldlevel : ' ') .
		($field702010 ? " left join {coursemetadata_info_data} d2 on d2.course = c.id and d2.fieldid = " . $field702010 : ' ') .
		($fieldmethodology ? " left join {coursemetadata_info_data} d3 on d3.course = c.id and d3.fieldid = " . $fieldmethodology : ' ') .
		" where visible = 1 and category > 0 " . $usql . " order by fullname ";

	$list = $DB->get_records_sql($sql, $params);

	if ($group_courses) {
		$grouped = array();
		foreach ($list as $r) {
			if (!isset($grouped[$r->id])) {
				$grouped[$r->id] = $r;
				$grouped[$r->id]->region_ids = array();
				if ($r->regionid) {
					$grouped[$r->id]->regionids[] = $r->regionid;
				}
			} else {
				if ($r->region) {
					if ($grouped[$r->id]->region) {
						$grouped[$r->id]->region .= ', ';
					}

					if ($r->regionid) {
						$grouped[$r->id]->regionids[] = $r->regionid;
					}

					$grouped[$r->id]->region .= $r->region;
				}
			}
		}

		return $grouped;
	}

	return $list;
}

function has_capability($name) {
	$systemcontext = \context_system::instance();
	return \has_capability('local/wa_learning_path:' . $name, $systemcontext);
}

function get_categories() {
	global $CFG;
	require_once($CFG->dirroot . '/lib/coursecatlib.php');
	return \coursecat::make_categories_list();
}

function get_regions() {
	global $DB;
	$sql = "select id, name from {local_regions_reg} where userselectable = 1";
	$l = $DB->get_records_sql($sql);
	$ret = array('0' => get_string('global', 'local_wa_learning_path'));
	foreach ($l as $r) {
		$ret[$r->id] = $r->name;
	}

	return $ret;
}

function get_methodologies() {
	global $DB, $CFG;

	$field = $DB->get_record('coursemetadata_info_field', array('shortname' => WA_LEARNING_PATH_CUSTOM_FIELD_METHODOLOGY));
	if (empty($field)) {
		return array();
	}

	require_once($CFG->dirroot . '/local/coursemetadata/field/' . $field->datatype . '/field.class.php');
	$newfield = 'coursemetadata_field_' . $field->datatype;
	$object = new $newfield((int) $field->id);
	
	return $object->options;
}

function get_course_methodologie_icon($courseid) {
	global $DB, $CFG;

	$field = $DB->get_record('coursemetadata_info_field', array('shortname' => WA_LEARNING_PATH_CUSTOM_FIELD_METHODOLOGY));
	if (empty($field)) {
		return array();
	}

	require_once($CFG->dirroot . '/local/coursemetadata/field/' . $field->datatype . '/field.class.php');
	$newfield = 'coursemetadata_field_' . $field->datatype;
	$object = new $newfield((int) $field->id, (int) $courseid);
	
	return $object->display_data();
}

function html_to_excel($html) {
	$html = str_replace("<br />", "\n", $html);
	$html = str_replace("<br/>", "\n", $html);
	$html = str_replace("<br>", "\n", $html);
	$html = str_replace("&nbsp;", " ", $html);
	$html = str_replace("</ol", "\n</ol", $html);
	$html = str_replace("</ul", "\n</ul", $html);
	$pattern = array("/<li[^\>]*>/");
	$replacement = array("\n* ");
	$html = preg_replace($pattern, $replacement, $html);
	return strip_tags($html);
}

function get_course_icon($courseid) {
	global $DB, $CFG;

	$field = $DB->get_record('coursemetadata_info_field', array('shortname' => WA_LEARNING_PATH_CUSTOM_FIELD_COURSE_ICON));

	if (empty($field)) {
		return array();
	}


	require_once("{$CFG->dirroot}/local/coursemetadata/lib.php");
	require_once("{$CFG->dirroot}/local/coursemetadata/field/{$field->datatype}/field.class.php");
	$fieldclassname = 'coursemetadata_field_' . $field->datatype;
	$fieldclass = new $fieldclassname($field->id, $courseid);

	return $fieldclass->display_data();
}

function get_user_region($userid = null) {
	global $DB, $USER;

	if (empty($userid)) {
		$userid = (int) $USER->id;
	}

	$sql = "select r.* FROM {local_regions_use} ur "
		. "INNER JOIN {local_regions_reg} r ON r.id = ur.regionid "
		. "WHERE ur.userid = :userid ";

	return $DB->get_record_sql($sql, array('userid' => (int) $userid));
}

/**
 * Returns the proper SQL to aggregate a field by joining with a specified delimiter
 *
 *
 */
function sql_group_concat($field, $delimiter = ', ', $unique = false) {
	global $DB;

	// if not supported, just return single value - use min()
	$sql = " MIN($field) ";

	switch ($DB->get_dbfamily()) {
		case 'mysql':
			// use native function
			$distinct = $unique ? 'DISTINCT' : '';
			$sql = " GROUP_CONCAT($distinct $field SEPARATOR '$delimiter') ";
			break;
		case 'postgres':
			// use custom aggregate function - must have been defined
			// in db/upgrade.php
			$distinct = $unique ? 'TRUE' : 'FALSE';
			$sql = " GROUP_CONCAT($field, '$delimiter', $distinct) ";
			break;
		case 'mssql':
			$distinct = $unique ? 'DISTINCT' : '';
			$sql = " dbo.GROUP_CONCAT_D($distinct $field, '$delimiter') ";
			break;
	}

	return $sql;
}

/**
 * Returns the SQL to be used in order to CAST one column to CHAR
 *
 * @param string fieldname the name of the field to be casted
 * @return string the piece of SQL code to be used in your statement.
 */
function sql_cast2char($fieldname) {

	global $DB;

	$sql = '';

	switch ($DB->get_dbfamily()) {
		case 'mysql':
			$sql = ' CAST(' . $fieldname . ' AS CHAR) COLLATE utf8_bin';
			break;
		case 'postgres':
			$sql = ' CAST(' . $fieldname . ' AS VARCHAR) ';
			break;
		case 'mssql':
			$sql = ' CAST(' . $fieldname . ' AS NVARCHAR(MAX)) ';
			break;
		case 'oracle':
			$sql = ' TO_CHAR(' . $fieldname . ') ';
			break;
		default:
			$sql = ' ' . $fieldname . ' ';
	}

	return $sql;
}
