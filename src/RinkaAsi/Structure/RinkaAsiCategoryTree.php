<?php

require_once dirname(__FILE__) . '/../Util/RinkaAsiNodeWalker.php';
require_once dirname(__FILE__) . '/../RinkaAsiException.php';

class RinkaAsiCategoryTree {

    protected $relation = null;
    protected $description = null;
    protected $children = array();

    public function __construct($Node) {
        $NodeWalker = RinkaAsiNodeWalker::getInstance();
        $this->relation = $NodeWalker->getAttribute($Node, 'relation');
        $this->description = $NodeWalker->getAttribute($Node, 'description');

        $this->children = array();
        if ($Node->hasChildNodes()) {
            foreach ($Node->childNodes as $Child) {
                $this->children[] = new self($Child);
            }
        }
    }

    public function getChildren() {
        return $this->children;
    }

    public function hasChildren() {
        return count($this->children) > 0;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getRelation() {
        return $this->relation;
    }

    public function hasRelation() {
        return $this->relation !== null;
    }
}