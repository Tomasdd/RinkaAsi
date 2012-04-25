<?php

require_once dirname(__FILE__) . '/../Util/RinkaAsiNodeWalker.php';
require_once 'RinkaAsiField.php';

class RinkaAsiFieldsList {

    protected $categoryKey = null;
    protected $FieldsListNode = null;

    /**
     * @var RinkaAsiNodeWalker
     */
    protected $NodeWalker = null;

    /**
     * @var array       array of RinkaAsiField - cached fields
     */
    protected $fields = array();

    public function __construct($xml, $categoryKey) {
        $DomDocument = new DOMDocument();
        $DomDocument->loadXML($xml);

        $this->NodeWalker = RinkaAsiNodeWalker::getInstance();

        $MapNode = $this->NodeWalker->getChildNode($DomDocument, array('map'));

        $FieldsListNode = $this->NodeWalker->findChildWithAttributes(
            $MapNode,
            array('key' => $categoryKey),
            'field-list'
        );

        if ($FieldsListNode === null) {
            throw new RinkaAsiException(
                'field-list tag with attribute key="' . $categoryKey . '" not found on given XML',
                RinkaAsiException::E_DATA
            );
        }

        $version = $this->NodeWalker->getAttribute($MapNode, 'version');
        if ($version === null) {
            throw new RinkaAsiException(
                '"map" tag does not have required attribute "version" on fields XML structure',
                RinkaAsiException::E_DATA
            );
        } elseif ($version != RinkaAsi::version) {
            throw new RinkaAsiException(
                'Fields XML structure version does not match with RinkaAsi library version. Library version: '
                    . RinkaAsi::version . ', XML version: ' . $version,
                RinkaAsiException::E_VERSION
            );
        }

        $this->categoryKey = $categoryKey;
        $this->FieldsListNode = $FieldsListNode;
    }

    public function getCategoryKey()
    {
        return $this->categoryKey;
    }

    /**
     *
     * @param string $name
     * @throws RinkaAsiException
     * @return RinkaAsiField
     */
    public function getField($name) {
        if (!isset($this->fields[$name])) {
            $Child = $this->NodeWalker->findChildWithAttributes($this->FieldsListNode, array('name' => $name));
            if ($Child !== null) {
                $this->fields[$name] = new RinkaAsiField($Child, $this);
            }
        }

        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        } else {
            throw new RinkaAsiException('Field with name "' . $name . '" not found', RinkaAsiException::E_NAMES);
        }
    }

    public function hasField($name) {
        if (isset($this->fields[$name])) {
            return true;
        } else {
            $Child = $this->NodeWalker->findChildWithAttributes($this->FieldsListNode, array('name' => $name));
            return $Child !== null;
        }
    }

    public function getAllFields() {
        if ($this->FieldsListNode->hasChildNodes()) {
            foreach ($this->FieldsListNode->childNodes as $Child) {
                if (
                    $Child->hasAttributes()
                    && ($nameAttribute = $Child->attributes->getNamedItem('name')) !== null
                ) {
                    $name = $nameAttribute->nodeValue;
                    if (!isset($this->fields[$name])) {
                        $this->fields[$name] = new RinkaAsiField($Child, $this);
                    }
                }
            }
        }
        return $this->fields;
    }
}
