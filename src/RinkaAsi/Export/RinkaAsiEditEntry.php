<?php

require_once 'RinkaAsiInsertEntry.php';

class RinkaAsiEditEntry extends RinkaAsiInsertEntry {

    protected function getEntryType() {
        return 'edit';
    }
}
