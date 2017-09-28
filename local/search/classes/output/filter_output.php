<?php

namespace local_search\output;
use local_search\local\filters;

/**
 * Display filters
 */
class filter_output implements \templatable
{
    private $filters;

    public function __construct(filters $filters)
    {
        $this->filters = $filters;
    }

    public function export_for_template(\renderer_base $output)
    {
        $data = [];

        foreach ($this->filters->get_filters() as $filter) {

            $data[] = [
                'filtertitle' => $filter->getName(),
                'fieldname'   => $filter->get_field_name(),
                'options'     => $filter->getOptionsForTemplate()
            ];
        }

        return $data;
    }
}