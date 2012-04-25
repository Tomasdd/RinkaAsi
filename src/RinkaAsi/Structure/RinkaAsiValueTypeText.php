<?php

require_once 'RinkaAsiValueType.php';
require_once dirname(__FILE__) . '/../RinkaAsiException.php';

class RinkaAsiValueTypeText extends RinkaAsiValueType {

    /**
     * @var string      RegExp to validate passed value. NULL if no check is needed
     */
    protected $regexp = null;

    /**
     * @var array               array of possible options (suggestions) as arrays
     *                  array(
     *                      NAME => array('key' => KEY, 'name' => NAME, 'description' => DESCRIPTION),
     *                      ...
     *                  )
     */
    protected $options = array();

    public function getTypeName() {
        return 'text';
    }

    public function __construct(DOMNode $Node) {
        parent::__construct($Node);
        $NodeWalker = RinkaAsiNodeWalker::getInstance();

        $this->regexp = $NodeWalker->getAttribute($Node, 'regexp');

        if ($Node->hasChildNodes()) {
            foreach ($Node->childNodes as $Child) {
                if ($Child->nodeName == 'option') {
                    $name = $NodeWalker->getAttribute($Child, 'name');
                    if ($name === null) {
                        throw new RinkaAsiException(
                            'Options tag has no "name" attribute in fields XML structure',
                            RinkaAsiException::E_DATA
                        );
                    }
                    $key = $NodeWalker->getAttribute($Child, 'key');
                    if ($key === null) {
                        $key = $name;
                    }
                    $this->options[$name] = array(
                        'key' => $NodeWalker->getAttribute($Child, 'key'),
                        'name' => $NodeWalker->getAttribute($Child, 'name'),
                        'description' => $NodeWalker->getAttribute($Child, 'description'),
                    );
                }
            }
        }
    }

    public function getOptions() {
        return $this->options;
    }

    public function hasOptions() {
        return !empty($this->options);
    }

    public function getRegexp() {
        return $this->regexp;
    }

    public function validate($value) {
        if (
               ($this->regexp !== null && preg_match($this->regexp, $value) == 0 && !empty($value))
            || ($this->isRequired() && empty($value))
        ) {
            throw new RinkaAsiException(
                'Passed value "' . $value . '" should match the regular expression "'
                    . $this->regexp . '"',
                RinkaAsiException::E_VALUES
            );
        }
    }

    public function createDomNodesForValue($value, DOMDocument $DomDocument) {
        if (isset($this->options[$value])) {
            $value = $this->options[$value]['key'];
        }
        $Node = $DomDocument->createElement('value', htmlspecialchars($value));
        $Node->setAttribute('key', $this->key);
        return array($Node);
    }
}
