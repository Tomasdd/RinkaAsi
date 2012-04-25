<?php

class RinkaAsiException extends Exception {

    /**
     * Cannot connect to remote server
     */
    const E_CONNECTION = 1;

    /**
     * Some errors in configuration
     */
    const E_CONFIGURATION = 2;

    /**
     * Inner library exception - should never happen in production environment
     */
    const E_INNER = 3;

    /**
     * Error in data, for example in XML, loaded from the remote server
     */
    const E_DATA = 4;

    /**
     * Error in passed arguments - field or category by specified name not found
     */
    const E_NAMES = 5;

    /**
     * Wrong type or number of parameters passed to some method
     */
    const E_SEMANTIC = 6;

    /**
     * Wrong number of values passed to field, wrong type of some value or incorrect/unknown value
     */
    const E_VALUES = 7;

    /**
     * Some of required parameters were not given
     */
    const E_MISSING_VALUES = 8;

    /**
     * Version of remote server XML and current library does not match
     */
    const E_VERSION = 9;

    /**
     * Remote server returned error status
     */
    const E_REMOTE = 10;
}