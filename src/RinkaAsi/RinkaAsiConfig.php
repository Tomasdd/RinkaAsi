<?php

require_once 'RinkaAsiException.php';

class RinkaAsiConfig {
    /**
     * @var array
     */
    protected $configuration;

    public function __construct(array $options = array()) {
        $defaults = array(
            'remoteServerBaseUrl'    => 'http://rinka.lt/asi/',
            'remoteServerSubmitBaseUrl' => 'http://rinka.lt:8080/asi/',
            'exportPath'  => 'getAds',
            'mapPath' => 'categories',
            'treePath' => 'tree',
            'fieldsPath' => 'fields/[categoryKey]',
            'submitPath' => 'import',
            'cacheEnabled' => true,
            'username' => 'anonymous',
            'password' => 'anonymous',
        );

        $this->configuration = array_merge($defaults, $options);
    }



    public function getRemoteServerBaseUrl() {
        return $this->configuration['remoteServerBaseUrl'];
    }

    public function getRemoteServerSubmitBaseUrl() {
        return $this->configuration['remoteServerSubmitBaseUrl'];
    }

    public function getExportPath() {
        return $this->configuration['exportPath'];
    }

    public function getMapPath() {
        return $this->configuration['mapPath'];
    }

    public function getTreePath() {
        return $this->configuration['treePath'];
    }

    public function getSubmitPath() {
        return $this->configuration['submitPath'];
    }

    public function getFieldsPath($categoryKey) {
        $filename = $this->configuration['fieldsPath'];
        if (strpos($filename, '[categoryKey]') === false) {
            throw new RinkaAsiException(
                'fieldsPath in configuration should contain string "[categoryKey]"',
                RinkaAsiException::E_CONFIGURATION
            );
        }
        $filename = str_replace('[categoryKey]', $categoryKey, $filename);
        return $filename;
    }

    public function isCacheEnabled() {
        return (bool) $this->configuration['cacheEnabled'];
    }

    public function getUsername() {
        return $this->configuration['username'];
    }

    public function getPassword() {
        return $this->configuration['password'];
    }
}
