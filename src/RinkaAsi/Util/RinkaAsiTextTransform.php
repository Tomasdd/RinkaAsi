<?php

class RinkaAsiTextTransform {

    protected static $instance = null;

    protected $lt_symbols = array('ą', 'č', 'ę', 'ė', 'į', 'š', 'ų', 'ū', 'ž', 'Ą', 'Č', 'Ę', 'Ė', 'Į', 'Š', 'Ų', 'Ū', 'Ž');
    protected $ut_symbols = array('a', 'c', 'e', 'e', 'i', 's', 'u', 'u', 'z', 'A', 'C', 'E', 'E', 'I', 'S', 'U', 'U', 'Z');

    protected function __construct() {

    }
    protected function __clone() {

    }

    /**
     * @return RinkaAsiTextTransform
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function clearUTF($s) {
        if (function_exists('iconv')) {
            return @iconv("UTF-8", "ASCII//TRANSLIT", $s);
        } else {
            return str_replace($this->lt_symbols, $this->ut_symbols, $s);
        }
    }

    public function makeStringKey($s) {
        $s = $this->clearUTF($s);

        $pattern = array('/\<.*\>/', '/\[.*\]/', '/\(.*\)/', '/[^\w_0-9]/i', '/_+/');
        $replacement = array('', '', '', '_', '_');
        $s = preg_replace($pattern, $replacement, strtolower($s));
        $s = trim($s, '_');
        return $s;
    }

}