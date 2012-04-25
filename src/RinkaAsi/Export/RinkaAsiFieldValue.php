<?php

require_once dirname(__FILE__) . '/../Structure/RinkaAsiField.php';
require_once dirname(__FILE__) . '/../Structure/RinkaAsiValueType.php';

class RinkaAsiFieldValue {

    /**
     * @var array       array of associative arrays [['type' => RinkaAsiValueType, 'value' => mixed], ...]
     */
    protected $values;

    /**
     * @var RinkaAsiField
     */
    protected $Field;

    /**
     * @var DOMDocument
     */
    protected $DomDocument = null;


    public function __construct(DOMDocument $DomDocument, RinkaAsiField $Field, array $valueList) {
        if (!is_array($valueList)) {
            $valueList = array($valueList);
        }

        $Field->validate($valueList);

        $value = reset($valueList);
        foreach ($Field->getValueTypes() as $ValueType) {
            $this->values[] = array('type' => $ValueType, 'value' => $value);
            $value = next($valueList);
        }
        $this->Field = $Field;

        $this->DomDocument = $DomDocument;
    }

    public function getDomNode() {
        $Node = $this->DomDocument->createElement('field');
        $Node->setAttribute('key', $this->Field->getKey());
        $Node->setAttribute('name', $this->Field->getName());

        foreach ($this->values as $valuePair) {
            $ValueType = $valuePair['type'];
            $value = $valuePair['value'];
            $childNodeList = $ValueType->createDomNodesForValue($value, $this->DomDocument);

            foreach ($childNodeList as $ChildNode) {
                $Node->appendChild($ChildNode);
            }
        }
        return $Node;
    }
}
