<?php

require_once 'RinkaAsiValueType.php';
require_once dirname(__FILE__) . '/../RinkaAsiException.php';

class RinkaAsiValueTypeSelect extends RinkaAsiValueType {

    /**
     * @var array               array of possible options as arrays
     *                  array(
     *                      NAME => array('key' => KEY, 'name' => NAME, 'description' => DESCRIPTION),
     *                      ...
     *                  )
     */
    protected $options = array();

    public function getOptions() {
        return $this->options;
    }

    public function hasOption($option) {
        return isset($this->options[$option]);
    }

    public function getTypeName() {
        return 'select';
    }

    public function __construct(DOMNode $Node) {
        parent::__construct($Node);
        if (!$Node->hasChildNodes()) {
            throw new RinkaAsiException(
                ucfirst($this->getTypeName()) . ' value has no options in fields XML structure',
                RinkaAsiException::E_DATA
            );
        }
        $NodeWalker = RinkaAsiNodeWalker::getInstance();
        foreach ($Node->childNodes as $Child) {
            if ($Child->nodeName == 'option') {
                $key = $NodeWalker->getAttribute($Child, 'key');
                $name = $NodeWalker->getAttribute($Child, 'name');
                if ($key === null || $name === null) {
                    throw new RinkaAsiException(
                        'Options tag has no "key" or "name" attribute in fields XML structure',
                        RinkaAsiException::E_DATA
                    );
                }
                $this->options[$name] = array(
                    'key' => $key,
                    'name' => $name,
                    'description' => $NodeWalker->getAttribute($Child, 'description'),
                );
            }
        }
    }

    public function validate($value) {
        if (!array_key_exists($value, $this->options) && !($this->isRequired() && empty($value)))  {
            throw new RinkaAsiException(
                'Unknown ' . $this->getTypeName() . ' field (key = "' . $this->key . '") value "' . $value . '". '
                    . 'Possible values: "' . implode('", "', array_keys($this->options)) . '"',
                RinkaAsiException::E_VALUES
            );
        }
    }

    public function createDomNodesForValue($value, DOMDocument $DomDocument) {
        $text = $this->options[$value]['key'];
        $Node = $DomDocument->createElement('value', htmlspecialchars($text));
        $Node->setAttribute('key', $this->key);
        return array($Node);
    }

}
