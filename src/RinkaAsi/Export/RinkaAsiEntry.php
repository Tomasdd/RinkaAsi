<?php

abstract class RinkaAsiEntry {

    protected $localId = null;

    /**
     * @var DOMDocument
     */
    protected $DomDocument = null;

    public function __construct(DOMDocument $DomDocument) {
        $this->DomDocument = $DomDocument;
    }

    /**
     * Should return the DOM Node, which specifies entry action and parameters
     * @return DOMNode
     */
    public function getDomNode() {
        if ($this->localId === null) {
            throw new RinkaAsiException(
                'Local system ID not specified for entry',
                RinkaAsiException::E_MISSING_VALUES
            );
        }

        $EntryNode = $this->DomDocument->createElement('entry');
        $EntryNode->setAttribute('type', $this->getEntryType());
        $EntryNode->setAttribute('local-id', $this->localId);

        return $EntryNode;
    }

    /**
     * Entry type, ie "insert", "edit", "delete"
     * @return string
     */
    protected abstract function getEntryType();

    /**
     * Sets localId of advertisement. It must be unique for each advertisement and should be specified
     * for all entries. It is used to delete or edit advertisement later.
     * If InsertEntry will contain already existing localId, it will generate an error on remote
     * server. If you're editing advertisement, you should create EditEntry instead of InsertEntry.
     *
     * @param string $localId      ID of advertisement in your local system. Must be unique for each advertisement
     * @return
     */
    public function setLocalId($localId) {
        $this->localId = $localId;
    }

    /**
     * Validates entry before submission.
     *
     * @throws RinkaAsiException
     */
    public abstract function validate();
}
