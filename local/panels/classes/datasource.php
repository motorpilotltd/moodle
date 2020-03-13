<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 26/02/2019
 * Time: 14:54
 */

namespace local_panels;

abstract class datasource {

    protected $zoneprefix;
    protected $context;

    public function __construct($context, $zoneprefix) {
        $this->zoneprefix = $zoneprefix;
        $this->context = $context;
    }

    /**
     * @param $zonesize
     * @return mixed
     */
    public abstract function rendernextitem($zonesize);

    /**
     * @param $zonesize
     * @return mixed
     */
    public abstract function renderallitems($zonesize);

    /**
     * @param $zonesize
     * @return mixed
     */
    public abstract function renderitem($zonesize);

    /**
     * Configure the datasource object based on data that was persisted to the db.
     * @param $zonedata
     * @return mixed
     */
    public abstract function unpickle($zonedata);

    /**
     * Return data containing all configuration information for the data source
     * i.e. the contents of the form fields
     * @param string $zoneprefix Prefix that had been added to all form fields for this data source in this zone
     * @return array fieldname => value pairs to be used with form setdata and json encoding for storage
     */
    public abstract function pickle();

    /**
     * Preprocess form data e.g. setup draft file areas
     * @param $zonedata
     * @return array fieldname => value pairs to be used with form setdata and json encoding for storage
     */
    public function preprocessform($zonedata) {
        return $zonedata;
    }

    /**
     * Post process form data - e.g. save files
     * @param $zoneprefix string Prefix that had been added to all form fields for this data source in this zone
     * @return array fieldname => value pairs to be used with form setdata and json encoding for storage
     */
    public function postprocessform() {}

    // No such thing as static abstract - here for documentation only...
    // Add form elements to the $mform object to allow the user to configure this datasource within the given zone
    // $zoneprefix is the prefix to add to all form fields.
    //public static abstract function addtoform($mform, $zoneprefix, $multiple)

    /**
     * @return mixed
     */
    public function getmachinename() {
        $fullyqualified = get_class($this);
        return explode('_', explode('\\', $fullyqualified)[0])[1];
    }
}