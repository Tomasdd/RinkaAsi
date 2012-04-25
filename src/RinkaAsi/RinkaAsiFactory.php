<?php

require_once "Util/RinkaAsiDefaultCache.php";
require_once "Util/RinkaAsiDefaultWebClient.php";
require_once "Util/RinkaAsiCurlWebClient.php";
require_once "Structure/RinkaAsiCategories.php";
require_once "Structure/RinkaAsiCategoryTree.php";
require_once "Structure/RinkaAsiFieldsList.php";
require_once "Export/RinkaAsiExportDocument.php";
require_once "Export/RinkaAsiAuthentication.php";

class RinkaAsiFactory {

    /**
     * Available web clients
     */
    const WC_DEFAULT = 'wc_default';
    const WC_CURL = 'wc_curl';

    public function getWebClientObject($type = self::WC_DEFAULT) {

        switch ($type) {
            case self::WC_DEFAULT:
                return new RinkaAsiDefaultWebClient();
            case self::WC_CURL:
                return new RinkaAsiCurlWebClient();
        }

        throw new Exception(sprintf('Unknown web client type=%s', $type));
    }

    public function getCacheObject() {
        return new RinkaAsiDefaultCache();
    }

    public function getCategoriesObject($param1, $param2) {
        return new RinkaAsiCategories($param1, $param2);
    }

    public function getCategoryTreeObject($param1) {
        return new RinkaAsiCategoryTree($param1);
    }

    public function getFieldsListObject($param1, $param2) {
        return new RinkaAsiFieldsList($param1, $param2);
    }

    public function getExportDocumentObject($param) {
        return new RinkaAsiExportDocument($param);
    }

    public function getAuthenticationObject($param1, $param2) {
        return new RinkaAsiAuthentication($param1, $param2);
    }
}
