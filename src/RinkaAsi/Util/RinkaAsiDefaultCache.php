<?php

require_once "RinkaAsiCacheInterface.php";
require_once dirname(__FILE__) . "/../RinkaAsiException.php";

class RinkaAsiDefaultCache implements RinkaAsiCacheInterface {

    public function load($name) {
        return null;
    }

    public function save($name, $value) {
        return false;
    }
}
