<?php

namespace local_search\local;


class filters
{
    const PARAM_NAME = '';

    /**
     * @var filter[]
     */
    private $filters = [];

    protected $active = true;

    /**
     * Get a list of all filters from DB
     */
    public function __construct()
    {
        $this->get_filters_from_db();

        if (false === $this->set_filters_from_request()) {
            $this->set_filters_from_session();
        }
    }

    protected function get_filters_from_db()
    {
        global $DB;

        $filterfields = 'shortname, name, param1';
        $filterrows = $DB->get_records_select('coursemetadata_info_field', "datatype IN ('menu', 'multiselect', 'iconsingle', 'iconmulti')", null, null, $filterfields);

        foreach ($filterrows as $row) {
            $filter = new filter($row);
            $this->filters[$filter->get_field_name()] = $filter;
        }
    }

    /**
     * @param $shortname
     * @return filter
     */
    public function get_filter($shortname)
    {
        return $this->filters[$shortname];
    }

    /**
     * @return filter[]
     */
    public function get_filters()
    {
        return $this->filters;
    }

    /**
     * @return filter[]
     */
    public function get_active_filters()
    {
        $rtn = [];

        foreach ($this->filters as $filter) {
            if ($filter->is_active()) {
                $rtn[] = $filter;
            }
        }

        return $rtn;
    }
    public function get_applied_values()
    {
        $values = [];

        foreach ($this->filters as $filter) {
            $values = array_merge($values, $filter->getValue());
        }

        return $values;
    }


    protected function set_filters_from_request()
    {
        global $SESSION;

        // Check here for clear filters
        if (true === isset($_REQUEST['clearfilters'])) {
            unset($SESSION->localsearchfilters);
            return true;
        }

        // Check form was submitted
        if (false === isset($_REQUEST['applyfilters'])) {
            return false;
        }

        unset($SESSION->localsearchfilters);

        if (isset($_REQUEST['filter']) && is_array($_REQUEST['filter'])) {
            foreach ($_REQUEST['filter'] as $fieldname => $appliedoptions) {
                $this->get_filter($fieldname)->setValues($appliedoptions);
            }
        }
        $SESSION->localsearchfilters = serialize($this->filters);

        return true;
    }

    protected function set_filters_from_session()
    {
        global $SESSION;
        if (isset($SESSION->localsearchfilters)) {

            $newfilters = [];
            $filters = unserialize($SESSION->localsearchfilters);

            // sanity check we have something useful
            if (is_array($filters) && count($filters) > 0) {
                // test that each object in array is a filter as expected... as session cannot be trusted
                /** @var filter $filter */
                foreach ($filters as $filter) {
                    // assert we have filter instance
                    if (false === $filter instanceof filter) {
                        continue;
                    }
                    // does this filter exist in current filterlist (initialised from DB)
                    if (false === isset($this->filters[$filter->get_field_name()])) {
                        continue;
                    }
                    // add to new filters
                    $newfilters[] = $filter;
                }
            }

            if (count($newfilters) > 0) {
                $this->filters = $newfilters;
            }
        }
    }

    public function active()
    {
        return count($this->get_active_filters()) > 0;
    }
}