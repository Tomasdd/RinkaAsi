<?php

require_once dirname(__FILE__) . '/../Util/RinkaAsiNodeWalker.php';
require_once dirname(__FILE__) . '/../RinkaAsiException.php';
require_once 'RinkaAsiValueTypeCheckbox.php';
require_once 'RinkaAsiValueTypeMultiselect.php';
require_once 'RinkaAsiValueTypeSelect.php';
require_once 'RinkaAsiValueTypeText.php';
require_once 'RinkaAsiValueTypeCityList.php';

abstract class RinkaAsiValueType {

    protected $Node;
    protected $key;

    protected $required;

    public function __construct(DOMNode $Node) {
        $this->Node = $Node;
        $this->key = RinkaAsiNodeWalker::getInstance()->getAttribute($Node, 'key');
        $this->required = RinkaAsiNodeWalker::getInstance()->getAttribute($Node, 'required');

        if ($this->key === null) {
            throw new RinkaAsiException(
                'Key not specified for value tag in fields XML structure',
                RinkaAsiException::E_DATA
            );
        }
        $type = RinkaAsiNodeWalker::getInstance()->getAttribute($Node, 'type');
        if ($type != $this->getTypeName()) {
            throw new RinkaAsiException(
                'Value of attribute "type" should be "' . $this->getTypeName()
                    . '" for tag passed to ' . get_class($this) . ' object',
                RinkaAsiException::E_INNER
            );
        }
    }

    /**
     * Checks for passed value validity
     *
     * @param mixed $value      passed value, can be boolean / string / array (for multiselect) etc.
     * @return
     * @throws RinkaAsiException    if passed value is not valid
     */
    public abstract function validate($value);

    /**
     * Returns array of DOMNode objects, representing selected field value
     *
     * @param array         $value          passed value, can be boolean / string / array (for multiselect) etc.
     * @param DOMDocument   $DomDocument
     * @return mixed                        array of DOMNode, usually of 1 element
     */
    public abstract function createDomNodesForValue($value, DOMDocument $DomDocument);

    /**
     *
     * @return string       name of type, ie "checkbox", "text", "select", "multiselect"
     */
    public abstract function getTypeName();

    /**
     *
     * @return array
     */
    public function getOptions() {
        return (isset($this->options) && !empty($this->options)) ? $this->options : array();
    }

    /**
     *
     * @return boolean
     */
    public function isRequired() {
        return ((int) $this->required) === 1;
    }

    /**
     * Creates RinkaAsiValue object depending on given node
     *
     * @param DOMNode $Node     field node, must have "type" attribute
     * @return RinkaAsiValue    object of some more specific class, extending RinkaAsiValue
     */
    public static function createInstance(DOMNode $Node) {
        $type = RinkaAsiNodeWalker::getInstance()->getAttribute($Node, 'type');
        if ($type === null) {
            throw new RinkaAsiException(
                'Attribute "type" not found in field tag.',
                RinkaAsiException::E_DATA
            );
        }
        switch ($type) {
            case 'checkbox':
            	return new RinkaAsiValueTypeCheckbox($Node);
                break;
            case 'multiselect':
                return new RinkaAsiValueTypeMultiselect($Node);
                break;
            case 'select':
                return new RinkaAsiValueTypeSelect($Node);
                break;
            case 'text':
                return new RinkaAsiValueTypeText($Node);
                break;
            case 'city_list':
                return new RinkaAsiValueTypeCityList($Node);
                break;
            default:
                throw new RinkaAsiException(
                    'Value of attribute "type" not recognized: "' . $type . '"',
                    RinkaAsiException::E_DATA
                );
        }
    }
}
