<?php

require_once "RinkaAsiException.php";
require_once "RinkaAsiFactory.php";
require_once "RinkaAsiConfig.php";
require_once 'Import/RinkaAsiFilter.php';
require_once 'Import/RinkaAsiAnnouncement.php';

class RinkaAsi {
    /**
     * version of XML specification - should be the same as in XML
     */
    const version = '1.1';

    /**
     * @var RinkaAsiWebClientInterface
     */
    protected $WebClient = null;

    /**
     * @var RinkaAsiCacheInterface
     */
    protected $Cache = null;

    /**
     * @var RinkaAsiCategories
     */
    protected $Categories = null;

    /**
     * @var RinkaAsiCategoryTree
     */
    protected $CategoryTree = null;

    /**
     * @var array of RinkaAsiFieldsList
     */
    protected $fieldsLists = array();

    /**
     * @var RinkaAsiFactory
     */
    protected $Factory = null;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var RinkaAsiAuthentication
     */
    protected $Authentication = null;


    public function __construct(RinkaAsiConfig $Config = null, RinkaAsiFactory $Factory = null) {
        if ($Config === null) {
            $Config = new RinkaAsiConfig();
        }
        $this->Config = $Config;

        if ($Factory === null) {
            $Factory = new RinkaAsiFactory();
        }
        $this->Factory = $Factory;

        $this->WebClient = $Factory->getWebClientObject('wc_curl');
        $this->Cache = $Factory->getCacheObject();
    }

    public function getCategories() {
        if ($this->Categories === null) {
            $mapAsText = $this->loadMap();
            $this->Categories = $this->Factory->getCategoriesObject($mapAsText, $this);
        }
        return $this->Categories;
    }

    public function getCategoryTree() {
        if ($this->CategoryTree === null) {
            $treeAsText = $this->loadTree();
            $DomDocument = new DOMDocument();
            $DomDocument->loadXML($treeAsText);

            $NodeWalker = RinkaAsiNodeWalker::getInstance();

            $TreeNode = $NodeWalker->getChildNode($DomDocument, array('tree'));

            if ($TreeNode == null) {
                throw new RinkaAsiException(
                    '"tree" tag not found on tree XML structure',
                    RinkaAsiException::E_DATA
                );
            }

            $version = $NodeWalker->getAttribute($TreeNode, 'version');
            if ($version === null) {
                throw new RinkaAsiException(
                    '"tree" node does not have required attribute "version" on tree XML structure',
                    RinkaAsiException::E_DATA
                );
            } elseif ($version != RinkaAsi::version) {
                throw new RinkaAsiException(
                    'Tree XML structure version does not match with RinkaAsi library version. Library version: '
                        . RinkaAsi::version . ', XML version: ' . $version,
                    RinkaAsiException::E_VERSION
                );
            }

            $this->CategoryTree = $this->Factory->getCategoryTreeObject($TreeNode);
        }
        return $this->CategoryTree;
    }

    public function getFieldsListForCategory($categoryKey) {
        if (!isset($this->fieldsLists[$categoryKey])) {
            $fieldsAsText = $this->loadFieldsListForCategory($categoryKey);
            $this->fieldsLists[$categoryKey] = $this->Factory->getFieldsListObject($fieldsAsText, $categoryKey);
        }
        return $this->fieldsLists[$categoryKey];
    }

    public function createExportDocument() {
        return $this->Factory->getExportDocumentObject($this->getAuthentication());
    }

    /**
     * Returns an array which contains two elements - totalAds and ads. In element 'ads'
     * are stored RinkaAsiAnnouncement objects.
     *
     * @param RinkaAsiFilter $filter
     * @return array
     */
    public function getFilteredAnnouncements(RinkaAsiFilter $filter) {
        $params = array(
            'filter'         => $filter->toQueryParams($this),
            'authentication' => $this->getAuthentication()->getCredentials(),
        );

        $url = $this->Config->getRemoteServerBaseUrl()
             . $this->Config->getExportPath()
             . '?' . http_build_query($params, null, '&');

        $response = $this->WebClient->get($url);

        try {
            $sxe = new SimpleXMLElement($response);
        } catch(Exception $e) {
            throw new RinkaAsiException('Bad response from server while receiving ads: ' . $response);
        }

        $ads = array();
        foreach ($sxe->children() as $ad) {
            $o = new RinkaAsiAnnouncement();

            $o->setId((int) $ad->attributes()->id);
            $o->setCategory(explode('/', (string) $ad->category));
            $o->setPublishDate((string) $ad->publishDate);
            $o->setUrl((string) $ad->url);
            $o->setDescription((string) $ad->description);
            $o->setTitle((string) $ad->title);
            $o->setPreviewImage((string) $ad->previewImage);

            $_imageList = $ad->imageList->image;
            $imageList = array();
            foreach ($_imageList as $image) {
                $imageList[] = (string) $image;
            }
            $o->setImageList($imageList);

            $fieldList = array();
            foreach ($ad->fieldList->field as $field) {
                $value = array();
                foreach ($field->value as $item) {
                    $value[] = (string) $item;
                }
                if (count($value) === 1) {
                    $value = reset($value);
                }
                $fieldList[] = array(
                    'title' => (string) $field->title,
                    'value' => $value,
                );
            }
            $o->setFieldList($fieldList);

            $ads[] = $o;
        }

        return array(
            'totalAds' => (int) $sxe->attributes()->totalAds,
            'ads'      => $ads,
        );
    }

    /**
     * Sends export document with added entries (insert, edit and delete operations) to remote server
     *
     * @param RinkaAsiExportDocument $ExportDocument    Export document with wntries to export
     * @param array                  $parameters        Additional parameters to post to server
     * @return string                                   returns response contents from remote server on success
     * @throws RinkaAsiException                        throws on failure, even if some ads were successfully exported
     */
    public function submitExportDocument(RinkaAsiExportDocument $ExportDocument, $parameters = array()) {
        $url = $this->Config->getRemoteServerSubmitBaseUrl() . $this->Config->getSubmitPath();
        $content = $this->WebClient->post(
            $url,
            array_merge((array) $parameters, array('export-document' => $ExportDocument->getXml()))
        );

        try {
            $sxe = new SimpleXMLElement($content);
        } catch (Exception $e) {
            throw new RinkaAsiException(
            	'Status message from remote server is not valid XML: "'
                    . $content . '"', RinkaAsiException::E_REMOTE
            );
        }

        $status = (string) $sxe->attributes()->status;
        $summary = (string) $sxe->attributes()->summary;

        if ($status === 'OK') {
            return array(
                'summary' => $summary,
                'status'  => $status,
            );
        } else {
            throw new RinkaAsiException(
            	'Error status message from remote server while sending export document: "'
                    . $summary . '"', RinkaAsiException::E_REMOTE
            );
        }
    }

    /**
     *
     * @return RinkaAsiAuthentication
     */
    protected function getAuthentication() {
        if ($this->Authentication === null) {
            $this->Authentication = $this->Factory->getAuthenticationObject(
                $this->Config->getUsername(),
                $this->Config->getPassword()
            );
        }
        return $this->Authentication;
    }


    protected function loadFromCache($name) {
        if ($this->Cache && $this->Config->isCacheEnabled()) {
            return $this->Cache->load($name);
        } else {
            return null;
        }
    }

    protected function saveToCache($name, $value) {
        if ($this->Cache && $this->Config->isCacheEnabled()) {
            return $this->Cache->save($name, $value);
        } else {
            return false;
        }
    }

    /**
     * Loads categories list and city list from remote server. If found on cache, cached version will be used.
     *
     * @return  string  xml as text from remote server or cache, unmodified
     */
    protected function loadMap() {
        $content = $this->loadFromCache('map');

        if ($content === null) {        // if haven't found in cache - load it
            $url = $this->Config->getRemoteServerBaseUrl() . $this->Config->getMapPath();
            $content = $this->WebClient->get($url);
            $this->saveToCache('map', $content);
        }

        return $content;
    }

    /**
     * Loads category tree - relation between visible categories, which are likely to change at any moment, and
     * RinkaAsi categories, used in filtering, inserting announcements etc.
     * If found on cache, cached version will be used.
     *
     * @return  string  xml as text from remote server or cache, unmodified
     */
    protected function loadTree() {
        $content = $this->loadFromCache('tree');

        if ($content === null) {        // if haven't found in cache - load it
            $url = $this->Config->getRemoteServerBaseUrl() . $this->Config->getTreePath();
            $content = $this->WebClient->get($url);
            $this->saveToCache('tree', $content);
        }

        return $content;
    }

    /**
     * Loads fields list for category from remote server. If found on cache, cached version will be used.
     *
     * @return  string  fields list as text from remote server or cache, unmodified
     */
    protected function loadFieldsListForCategory($categoryKey) {
        $content = $this->loadFromCache('fields_' . $categoryKey);

        if ($content === null) {        // if haven't found in cache - load it
            $url = $this->Config->getRemoteServerBaseUrl() . $this->Config->getFieldsPath($categoryKey);
            $content = $this->WebClient->get($url);
            $this->saveToCache('fields_' . $categoryKey, $content);
        }

        return $content;
    }
}



