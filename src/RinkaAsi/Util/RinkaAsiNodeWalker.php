<?php

class RinkaAsiNodeWalker {

    protected static $instance = null;

    protected function __construct() {

    }
    protected function __clone() {

    }

    /**
     *
     * @return RinkaAsiNodeWalker
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function findChildWithAttributes(DOMNode $Node, array $requiredAttributes, $requiredTagName = null) {
        if ($Node->hasChildNodes()) {
            foreach ($Node->childNodes as $Child) {
                if ($requiredTagName !== null && $Child->nodeName != $requiredTagName) {    // check for required tag
                    continue;
                }

                if ($Child->hasAttributes()) {
                    foreach ($requiredAttributes as $attributeName => $attributeValue) {    // check for required attributes
                        $Attribute = $Child->attributes->getNamedItem($attributeName);
                        if ($Attribute === null || $Attribute->nodeValue != $attributeValue) {
                            continue 2;
                        }
                    }

                    return $Child;
                }
            }
        }
        return null;
    }

    public function getAttribute(DOMNode $Node, $attributeName) {
        if (
            $Node->hasAttributes()
            && ($Attribute = $Node->attributes->getNamedItem($attributeName)) !== null
        ) {
            return $Attribute->nodeValue;
        } else {
            return null;
        }
    }

    public function getChildNode(DOMNode $Node, array $path) {
        if ($Node === null) {
            return null;
        }
        if (empty($path)) {
            return $Node;
        }
        $name = array_shift($path);
        if ($Node->hasChildNodes()) {
            foreach ($Node->childNodes as $Child) {
                if ($Child->nodeName == $name) {
                    return $this->getChildNode($Child, $path);
                }
            }
        }
        return null;
    }
}



