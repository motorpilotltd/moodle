<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 26/02/2019
 * Time: 14:54
 */

namespace local_panels;

abstract class layout {
    const ZONESIZE_SMALL = 'small';
    const ZONESIZE_LARGE = 'large';

    public abstract function getzonecount();

    public abstract function getzonesize($zonenumber);

    public abstract function zonecantakearray($zonenumber);

    public abstract function render($data);

    public function is_backgroundpanel() {
        return false;
    }

    public function getmachinename() {
        $fullyqualified = get_class($this);
        return explode('_', explode('\\', $fullyqualified)[0])[1];
    }
}