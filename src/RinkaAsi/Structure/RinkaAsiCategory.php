<?php

require_once dirname(__FILE__) . '/../RinkaAsiException.php';
require_once dirname(__FILE__) . '/../Util/RinkaAsiNodeWalker.php';

class RinkaAsiCategory {
    /**
     * @var DomNode
     */
    protected $CategoryNode = null;

    /**
     * @var RinkaAsiCategory
     */
    protected $ParentCategory = null;

    /**
     * @var array       array of RinkaAsiCategory - cached children
     */
    protected $children = array();

    protected $key = null;
    protected $name = null;
    protected $description = null;

    /**
     * @var RinkaAsiFieldsList
     */
    protected $FieldsList = null;

    /**
     * @var RinkaAsiNodeWaker
     */
    protected $NodeWalker = null;

    /**
     *
     * @param DomNode          $CategoryNode
     * @param RinkaAsiCategory $ParentCategory
     */
    public function __construct(DOMNode $CategoryNode, $ParentCategory) {
        $this->CategoryNode = $CategoryNode;
        $this->ParentCategory = $ParentCategory;

        $this->NodeWalker = RinkaAsiNodeWalker::getInstance();

        $this->key = $this->NodeWalker->getAttribute($CategoryNode, 'key');
        $this->name = $this->NodeWalker->getAttribute($CategoryNode, 'name');
        $this->description = $this->NodeWalker->getAttribute($CategoryNode, 'description');
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

    public function getCategoryArray() {
        $parentArray = array();
        if ($this->ParentCategory) {
            $parentArray = $this->ParentCategory->getCategoryArray();
        }
        $name = $this->getName();
        if (!empty($name)) {
            $parentArray[] = $name;
        }
        return $parentArray;
    }

    public function getCityKey(array $path) {
        if (empty($path)) {
            throw new RinkaAsiException('No path for getCity method provided!');
        }

        $cityList = $this->getCityList();
        foreach ($path as $value) {
            if (!isset($cityList[$value])) {
                throw new RinkaAsiException('Such city doesn\'t exist!');
            }
            $cityList = $cityList[$value];
        }

        return $cityList;
    }

    public function getCategory($name, $fullCategoryName = '') {
        if (is_array($name)) {
            if (empty($name)) {         // getCategory(array()) returns itself
                return $this;
            } else {
                $path = $name;
                $name = array_shift($path);
            }
        } else {
            $path = array();
        }

        if (!isset($this->children[$name])) {
            $Child = $this->NodeWalker->findChildWithAttributes($this->CategoryNode, array('name' => $name));
            if ($Child !== null) {
                $this->children[$name] = new RinkaAsiCategory($Child, $this);
            }
        }

        if (isset($this->children[$name])) {
            return $this->children[$name]->getCategory($path, $fullCategoryName);
        } else {
            throw new RinkaAsiException('Category with name "' . $name . ' ('. (is_array($fullCategoryName) ? implode('/', $fullCategoryName) : $fullCategoryName) .')" not found', RinkaAsiException::E_NAMES);
        }
    }

    public function hasChildren() {
        if (empty($this->children)) {
            return false;
        }

        return true;
    }

    public function hasCategory($name) {
        if (isset($this->children[$name])) {
            return true;
        } else {
            $Child = $this->NodeWalker->findChildWithAttributes($this->CategoryNode, array('name' => $name));
            return $Child !== null;
        }
    }

    public function getAllCategories() {
        if ($this->CategoryNode->hasChildNodes()) {
            foreach ($this->CategoryNode->childNodes as $Child) {
                if (
                    $Child->hasAttributes()
                    && ($nameAttribute = $Child->attributes->getNamedItem('name')) !== null
                ) {
                    $name = $nameAttribute->nodeValue;
                    if (!isset($this->children[$name])) {
                        $this->children[$name] = new RinkaAsiCategory($Child, $this);
                    }
                }
            }
        }
        return $this->children;
    }

    public function getFieldsList($categoryKey) {
        if ($this->ParentCategory === null) {
            throw new RinkaAsiException(
                'RinkaAsiCategory parent is null, RinkaAsiCategories object expected',
                RinkaAsiException::E_INNER
            );
        } else {
            return $this->ParentCategory->getFieldsList($categoryKey);
        }
    }

    public function getCityList() {
        if ($this->ParentCategory === null) {
            throw new RinkaAsiException(
                'RinkaAsiCategory parent is null, RinkaAsiCategories object expected',
                RinkaAsiException::E_INNER
            );
        } else {
            return $this->ParentCategory->getCityList();
        }
    }

    public function getLocationDescriptions() {
        if ($this->ParentCategory === null) {
            throw new RinkaAsiException(
                'RinkaAsiCategory parent is null, RinkaAsiCategories object expected',
                RinkaAsiException::E_INNER
            );
        } else {
            return $this->ParentCategory->getLocationDescriptions();
        }
    }

    protected function loadFieldsList() {
        if ($this->FieldsList === null) {
            $this->FieldsList = $this->getFieldsList($this->getKey());
        }
    }

    public function getField($name) {
        $this->loadFieldsList();
        return $this->FieldsList->getField($name);
    }

    public function hasField($name) {
        $this->loadFieldsList();
        return $this->FieldsList->hasField($name);
    }

    public function getAllFields() {
        $this->loadFieldsList();
        return $this->FieldsList->getAllFields();
    }


}
