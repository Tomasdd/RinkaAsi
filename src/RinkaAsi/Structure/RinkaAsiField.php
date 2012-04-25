<?php

require_once 'RinkaAsiFieldsList.php';
require_once 'RinkaAsiValueType.php';

class RinkaAsiField {
    /**
     * @var DomNode
     */
    protected $FieldNode = null;

    /**
     * @var array       array of RinkaAsiValueType
     */
    protected $valueTypeList = null;

    protected $key = null;
    protected $name = null;
    protected $description = null;
    protected $required = null;

    /**
     * @var RinkaAsiFieldsList      "parent" fields list object
     */
    protected $FieldsList = null;

    /**
     * @var RinkaAsiNodeWalker
     */
    protected $NodeWalker = null;

    /**
     *
     * @param DomNode          $CategoryNode
     * @param RinkaAsiCategory $ParentCategory
     */
    public function __construct(DomNode $FieldNode, RinkaAsiFieldsList $FieldsList) {
        $this->FieldNode = $FieldNode;
        $this->FieldsList = $FieldsList;

        $this->NodeWalker = RinkaAsiNodeWalker::getInstance();

        $this->key = $this->NodeWalker->getAttribute($FieldNode, 'key');
        $this->name = $this->NodeWalker->getAttribute($FieldNode, 'name');
        $this->description = $this->NodeWalker->getAttribute($FieldNode, 'description');
        $this->required = $this->NodeWalker->getAttribute($FieldNode, 'required');
    }

    public function getKey() {
        return $this->key;
    }
    public function getName() {
        return $this->name;
    }
    public function getDescription() {
        return $this->description;
    }
    /**
     *
     * @return boolean
     */
    public function isRequired() {
        return ((int) $this->required) === 1;
    }

    protected function loadValueTypes() {
        if ($this->valueTypeList !== null) {
            return;
        }
        $this->valueTypeList = array();
        if ($this->FieldNode->hasChildNodes()) {
            foreach ($this->FieldNode->childNodes as $Child) {
                if ($Child->nodeName == 'value') {
                    $this->valueTypeList[] = RinkaAsiValueType::createInstance($Child);
                }
            }
        }
    }

    public function getFieldsList()
    {
        return $this->FieldsList;
    }

    public function getValueTypes() {
        $this->loadValueTypes();
        return $this->valueTypeList;
    }

    public function isValid($valueList) {
        try {
            $this->validate($valueList);
            return true;
        } catch (RinkaAsiException $Exception) {
            return false;
        }
    }

    public function validate($valueList) {
        $this->loadValueTypes();

        if (!is_array($valueList)) {
            $valueList = array($valueList);
        }

        if (count($this->valueTypeList) != count($valueList)) {
            throw new RinkaAsiException(
                'Number of field values doesn\'t match required number of values. '
                    . 'Field: ' . $this->name . ', expected number of values: '
                    . count($this->valueTypeList) . ', number of values passed: '
                    . count($valueList),
                RinkaAsiException::E_VALUES
            );
        }

        $value = reset($valueList);
        foreach ($this->valueTypeList as $ValueType) {
            $ValueType->validate($value);
            $value = next($valueList);
        }
    }
}


