<?php

require_once 'RinkaAsiCategory.php';
require_once dirname(__FILE__) . '/../Util/RinkaAsiNodeWalker.php';
require_once dirname(__FILE__) . '/../RinkaAsiException.php';
require_once dirname(__FILE__) . '/../RinkaAsi.php';

class RinkaAsiCategories extends RinkaAsiCategory {

    /**
     * @var RinkaAsi
     */
    protected $RinkaAsi = null;
    protected $CityListNode = null;

    protected $locationDescriptions = array();

    public function __construct($xml, RinkaAsi $RinkaAsi) {
        $this->RinkaAsi = $RinkaAsi;

        $DomDocument = new DOMDocument();
        $DomDocument->loadXML($xml);

        $NodeWalker = RinkaAsiNodeWalker::getInstance();

        $MapNode = $NodeWalker->getChildNode($DomDocument, array('map'));
        $CategoryNode = $NodeWalker->getChildNode($MapNode, array('category-list'));

        if ($CategoryNode == null) {
            throw new RinkaAsiException(
                '"category-list" tag not found on categories XML structure',
                RinkaAsiException::E_DATA
            );
        }

        $version = $NodeWalker->getAttribute($MapNode, 'version');
        if ($version === null) {
            throw new RinkaAsiException(
                '"map" node does not have required attribute "version" on categories XML structure',
                RinkaAsiException::E_DATA
            );
        } elseif ($version != RinkaAsi::version) {
            throw new RinkaAsiException(
                'Categories XML structure version does not match with RinkaAsi library version. Library version: '
                    . RinkaAsi::version . ', XML version: ' . $version,
                RinkaAsiException::E_VERSION
            );
        }

        $CityListNode = $NodeWalker->getChildNode($MapNode, array('city-list'));
        if ($CityListNode == null) {
            throw new RinkaAsiException(
                '"city-list" node not found on categories XML structure',
                RinkaAsiException::E_DATA
            );
        }
        $this->loadCities($CityListNode);

        parent::__construct($CategoryNode, null);
    }

    public function getFieldsList($categoryKey) {
        return $this->RinkaAsi->getFieldsListForCategory($categoryKey);
    }

    protected function writeLocationDescription($key, $value) {
        $this->locationDescriptions[$key] = $value;
    }

    protected function loadCities(DOMNode $CityListNode) {
        $NodeWalker = RinkaAsiNodeWalker::getInstance();
        $this->cityList = array();
        if ($CityListNode->hasChildNodes()) {
            foreach ($CityListNode->childNodes as $CountryChild) {
                $countryName = $NodeWalker->getAttribute($CountryChild, 'name');
                $countryDescription = $NodeWalker->getAttribute($CountryChild, 'description');
                $this->writeLocationDescription($countryName, $countryDescription);
                if (
                       $CountryChild->nodeName == 'country'
                    && $countryName !== null
                    && $CountryChild->hasChildNodes()
                ) {
                    foreach ($CountryChild->childNodes as $Child) {
                        if ($Child->nodeName == 'city') {
                            $name = $NodeWalker->getAttribute($Child, 'name');
                            $key = $NodeWalker->getAttribute($Child, 'key');
                            $description = $NodeWalker->getAttribute($Child, 'description');
                            if ($name !== null && $key !== null) {
                                $this->cityList[$countryName][$name] = $key;
                                $this->writeLocationDescription($name, $description);
                            }
                        }
                    }
                    ksort($this->cityList[$countryName]);
                }
            }
        }
    }

    public function getCityList() {
        return $this->cityList;
    }

    public function getLocationDescriptions() {
        return $this->locationDescriptions;
    }
}
