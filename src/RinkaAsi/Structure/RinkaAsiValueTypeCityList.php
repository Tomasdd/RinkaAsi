<?php

require_once 'RinkaAsiValueType.php';
require_once dirname(__FILE__) . '/../RinkaAsiException.php';

class RinkaAsiValueTypeCityList extends RinkaAsiValueType {

    public function getTypeName() {
        return 'city_list';
    }

    public function validate($value) {
        return true;
    }

    public function createDomNodesForValue($value, DOMDocument $DomDocument) {
        $nodeList = array();
        $Node = $DomDocument->createElement('value', '');
        $Node->setAttribute('key', $this->getTypeName());

        foreach ($value as $v) {
            if (!empty($v['value'])) {
                $child = $DomDocument->createElement($v['type'], $v['value']);
                if (!array_key_exists('custom', $v)) {
                    $v['custom'] = 'none';
                }

                $child->setAttribute('custom', $v['custom']);
                $Node->appendChild($child);
            }
        }

        return array($Node);
    }

}