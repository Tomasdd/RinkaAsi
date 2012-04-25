<?php

require_once 'RinkaAsiEntry.php';
require_once dirname(__FILE__) . '/../Structure/RinkaAsiCategory.php';

class RinkaAsiDeleteEntry extends RinkaAsiEntry {

    public function __construct(DOMDocument $DomDocument, $localId = null) {
        parent::__construct($DomDocument);

        $this->setLocalId($localId);
    }

    protected function getEntryType() {
        return 'delete';
    }

    /**
     * Validates entry before submission.
     *
     * @throws RinkaAsiException
     */
    public function validate() {
        // do nothing
    }
}
