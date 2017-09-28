<?php

namespace local_search\local;

/**
 */
class filter
{
    private $name;

    private $shortname;

    private $options = [];

    public function __construct($dbrow)
    {
        $this->name = $dbrow->name;
        $this->shortname = $dbrow->shortname;
        $this->type = 'metadata';

        foreach (explode("\n", $dbrow->param1) as $option) {

            $option = trim($option);

            if (strlen($option)==0) {
                continue;
            }

            $this->options[$option] = [
                'optionvalue' => $option,
                'checked'     => false
            ];
        }
    }

    public function getName()
    {
        return $this->name;
    }


    public function get_field_name()
    {
        return $this->shortname;
    }

    public function setValues($appliedoptions)
    {
        foreach ($appliedoptions as $optionValue) {
            if (isset($this->options[$optionValue])) {
                $this->options[$optionValue]['checked'] = true;
            }
        }
    }

    public function getValue()
    {
        $rtn = [];

        foreach ($this->options as $option) {
            if ($option['checked']) {
                $rtn[] = $option['optionvalue'];
            }
        }

        return $rtn;
    }

    public function optionChecked($optionValue)
    {
        if (false === isset($this->options[$optionValue])) {
            return false;
        }

        return $this->options[$optionValue]['checked'];
    }

    public function getOptionsForTemplate()
    {
        return array_values($this->options);
    }

    public function is_active()
    {
        return false === empty($this->getValue());
    }
}