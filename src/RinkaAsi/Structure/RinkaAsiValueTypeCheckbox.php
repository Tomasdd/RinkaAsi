<?php

require_once 'RinkaAsiValueType.php';
require_once dirname(__FILE__) . '/../RinkaAsiException.php';

class RinkaAsiValueTypeCheckbox extends RinkaAsiValueType {

    public function getTypeName() {
        return 'checkbox';
    }

    protected function fixValue($value) {
        if (in_array($value, array('true', 'True', 'TRUE', 1, -1, '1', 'on', 'ON', 'On', 'Yes', 'YES', 'yes'), true)) {
            $value = true;
        } elseif (in_array($value, array('', 'false', 'False', 'FALSE', 0, '0', 'off', 'OFF', 'Off', 'no', 'NO', 'No'), true)) {
            $value = false;
        }
        return $value;
    }

    public function validate($value) {
        $value = $this->fixValue($value);
        if (!is_bool($value)) {
            throw new RinkaAsiException(
                'Value of "checkbox" field should be boolean true or boolean false',
                RinkaAsiException::E_VALUES
            );
        }
    }

    public function createDomNodesForValue($value, DOMDocument $DomDocument) {
        $value = $this->fixValue($value);
        $text = $value ? 'on' : '';
        $Node = $DomDocument->createElement('value', htmlspecialchars($text));
        $Node->setAttribute('key', $this->key);
        return array($Node);
    }

}
