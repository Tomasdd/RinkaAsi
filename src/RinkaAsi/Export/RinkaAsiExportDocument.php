<?php

require_once dirname(__FILE__) . '/../RinkaAsi.php';
require_once 'RinkaAsiInsertEntry.php';
require_once 'RinkaAsiEditEntry.php';
require_once 'RinkaAsiDeleteEntry.php';


class RinkaAsiExportDocument {

    /**
     * @var array       array of RinkaAsiEntry
     */
    protected $entries = array();

    /**
     * @var DOMDocument
     */
    protected $DomDocument = null;

    protected $generatedXml = null;

    public function __construct(RinkaAsiAuthentication $Authentication) {
        $this->DomDocument = new DOMDocument('1.0', 'UTF-8');
        $this->Authentication = $Authentication;
    }

    public function createNewInsertEntry(RinkaAsiCategory $Category, $localId = null) {
        $Entry = new RinkaAsiInsertEntry($this->DomDocument, $Category);
        $Entry->setLocalId($localId);
        $this->addEntry($Entry);
        return $Entry;
    }

    public function createNewEditEntry(RinkaAsiCategory $Category, $localId = null) {
        $Entry = new RinkaAsiEditEntry($this->DomDocument, $Category);
        $Entry->setLocalId($localId);
        $this->addEntry($Entry);
        return $Entry;
    }

    public function createNewDeleteEntry($localId) {
        $Entry = new RinkaAsiDeleteEntry($this->DomDocument, $localId);
        $this->addEntry($Entry);
        return $Entry;
    }


    public function addEntry(RinkaAsiEntry $Entry) {
        $this->entries[] = $Entry;
        $this->generatedXml = null;     // clear catched XML - it's not valid anymore
    }

    public function removeEntry(RinkaAsiEntry $Entry) {
        $key = array_search($Entry, $this->entries);
        if ($key === false) {   // entry not found
            return false;
        } else {
            unset($this->entries[$key]);
            $this->generatedXml = null;     // clear catched XML - it's not valid anymore
            return true;
        }
    }


    public function getXml() {
        if ($this->generatedXml === null) {
            $ExportDocumentNode = $this->DomDocument->createElement('export-document');
            $ExportDocumentNode->setAttribute('version', RinkaAsi::version);

            $ExportDocumentNode->appendChild($this->Authentication->getDomNode($this->DomDocument));

            foreach ($this->entries as $Entry) {
                $Entry->validate();
                $ExportDocumentNode->appendChild($Entry->getDomNode());
            }

            if (isset($this->DomDocument->firstChild)) {
                $this->DomDocument->removeChild($this->DomDocument->firstChild);
            }

            $this->DomDocument->appendChild($ExportDocumentNode);

            //$this->DomDocument->formatOutput = true;
            $this->generatedXml = $this->DomDocument->saveXML();
        }

        return $this->generatedXml;
    }
}
