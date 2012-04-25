<?php

require_once 'RinkaAsiValueTypeSelect.php';

class RinkaAsiValueTypeMultiselect extends RinkaAsiValueTypeSelect {

    public function getTypeName() {
        return 'multiselect';
    }

    protected function fixValue($valueList) {
        if (!is_array($valueList)) {
            $valueList = array($valueList);
        }
        $valueList = array_unique($valueList);
        return $valueList;
    }

    public function validate($valueList) {
        $valueList = $this->fixValue($valueList);
        foreach ($valueList as $value) {
            parent::validate($value);
        }
    }

    public function createDomNodesForValue($valueList, DOMDocument $DomDocument) {
        $valueList = $this->fixValue($valueList);
        $nodeList = array();
        foreach ($valueList as $value) {
            $nodeList = array_merge($nodeList, parent::createDomNodesForValue($value, $DomDocument));
        }
        return $nodeList;
    }
}
