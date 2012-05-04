<?php

require_once 'RinkaAsiEntry.php';
require_once 'RinkaAsiFieldValue.php';
require_once dirname(__FILE__) . '/../Structure/RinkaAsiCategory.php';

class RinkaAsiInsertEntry extends RinkaAsiEntry {

    protected $Category = null;

    protected $skipContacts = false;
    protected $contacts = array();
    protected $images = array();
    protected $publishDate = null;
    protected $publishUntil = null;
    protected $fieldValues = array();
    protected $sourceUrl = null;
    protected $sourceBase = null;
    protected $userIp = null;

    public function __construct(DOMDocument $DomDocument, RinkaAsiCategory $Category) {
        parent::__construct($DomDocument);

        $this->Category = $Category;

        $this->contacts = array(            // structure of contacts
            'phone' => array(),                 // 0 or more
            'email' => array(),                 // 0 or more
            'city' => array(),                  // 0 or more
            'name' => array(),                  // 0 or more
            'country' => array(),  // 0 or more
        );
        $this->setPublishDate();            // now
    }

    public function getCategory() {
        return $this->Category;
    }

    protected function getEntryType() {
        return 'insert';
    }

    public function getDomNode() {
        $EntryNode = parent::getDomNode();

        $CategoryNode = $this->DomDocument->createElement('category');
        $CategoryNode->setAttribute('key', $this->Category->getKey());
        $EntryNode->appendChild($CategoryNode);

        if ($this->publishDate !== null) {
            $Node = $this->DomDocument->createElement('publish-date', htmlspecialchars($this->publishDate));
            $EntryNode->appendChild($Node);
        }
        if ($this->publishUntil !== null) {
            $Node = $this->DomDocument->createElement('publish-until', htmlspecialchars($this->publishUntil));
            $EntryNode->appendChild($Node);
        }

        if ($this->sourceBase !== null) {
            $Node = $this->DomDocument->createElement('source-base', htmlspecialchars($this->sourceBase));
            $EntryNode->appendChild($Node);
        }

        if ($this->userIp !== null) {
            $Node = $this->DomDocument->createElement('user-ip', htmlspecialchars($this->userIp));
            $EntryNode->appendChild($Node);
        }

        if ($this->sourceUrl !== null) {
            $Node = $this->DomDocument->createElement('source-url', htmlspecialchars($this->sourceUrl));
            $EntryNode->appendChild($Node);
        }

        $ContactListNode = $this->DomDocument->createElement('contact-list');
        foreach ($this->contacts as $contactType => $contacts) {
            if (!empty($contacts)) {
                if (!is_array($contacts)) {
                    $contacts = array($contacts);
                }
                foreach ($contacts as $contact) {
                    $Node = $this->DomDocument->createElement($contactType, htmlspecialchars($contact));
                    $ContactListNode->appendChild($Node);
                }
            }
        }
        $EntryNode->appendChild($ContactListNode);

        if (!empty($this->images)) {
            $ImageListNode = $this->DomDocument->createElement('image-list');
            foreach ($this->images as $image) {
                $Node = $this->DomDocument->createElement('image', htmlspecialchars($image));
                $ImageListNode->appendChild($Node);
            }
            $EntryNode->appendChild($ImageListNode);
        }

        $FieldListNode = $this->DomDocument->createElement('field-list');
        foreach ($this->fieldValues as $FieldValue) {
            $FieldListNode->appendChild($FieldValue->getDomNode());
        }
        $EntryNode->appendChild($FieldListNode);

        return $EntryNode;
    }

    /**
     * Adds one or more contacts to the entry. If some contacts were added before, they will be merged
     * with the new ones. Some fields can have few values, some only one (ie City). In that case
     * field will be overwritten.
     * Example of use:
     * $Entry->addContact('miestas', array('lietuva', 'kaunas'));
     * $Entry->addContact('telefonas', '866633333');
     * $Entry->addContact(array(
     *      'telefonas' => '866644444',
     *      'telefonas' => '866655555',
     *      'miestas' => array('lietuva', 'vilnius'),         // one of pre-defined values
     * ));
     *          // now there are 3 phones (merged) and 1 city (overwritten)
     *
     *
     * @param mixed $name               associative array of name => value fields, or name of field
     * @param mixed $value [optional]   string or array of strings to be added
     * @return
     * @throws RinkaAsiException        if wrong arguments are passed or contact type name is unknown
     */
    public function addContact($name, $value = null) {
        static $contactsDictionary = array(     // possible names to define contact fields
            'telefonas' => 'phone',
            'telefonai' => 'phone',
            'el_pastas' => 'email',
            'miestas' => 'city',
            'miestai' => 'city',
            'vardas'  => 'name',
            'salis' => 'country',
        );

        if (!is_array($name) && $value === null) {
            throw new RinkaAsiException(
                'addContact() expects 2 arguments or associative array',
                RinkaAsiException::E_SEMANTIC
            );
        }
        if (is_array($name)) {
            $contacts = $name;
            foreach ($contacts as $name => $value) {
                $this->addContact($name, $value);
            }
        } else {
            if (!isset($contactsDictionary[$name])) {
                throw new RinkaAsiException(
                    'Unknown contacts field: "' . $name . '"',
                    RinkaAsiException::E_NAMES
                );
            }

            $key = $contactsDictionary[$name];

            $value = $this->checkContact($key, $value);

            if (is_array($this->contacts[$key])) {
                if (!in_array($value, $this->contacts[$key])) {
                    $this->contacts[$key][] = $value;
                }
            } else {
                $this->contacts[$key] = $value;
            }
        }
    }

    protected function checkContact($key, $value) {
        if ($key == 'city') {       // city must be one of pre-defined values
            $cityList = $this->Category->getCityList();
            if (!is_array($value) || count($value) < 2) {
                throw new RinkaAsiException(
                    'City should be specified as an array of 2 elements - country and city',
                    RinkaAsiException::E_NAMES
                );
            }
            if (isset($value['country']) && isset($value['city'])) {
                $country = $value['country'];
                $city = $value['city'];
            } else {
                $country = array_shift($value);
                $city = array_shift($value);
            }
            if (!isset($cityList[$country])) {
                throw new RinkaAsiException(
                    'Specified country name is not valid. Specified country name: "'
                        . $country . '", valid country names: "'
                        . implode('", "', array_keys($cityList)) . '"',
                    RinkaAsiException::E_NAMES
                );
            } elseif (!isset($cityList[$country][$city])) {
                throw new RinkaAsiException(
                    'Specified city name is not valid (looging in country "'
                        . $country . '"). Specified city name: "'
                        . $city . '", valid city names: "'
                        . implode('", "', array_keys($cityList[$country])) . '"',
                    RinkaAsiException::E_NAMES
                );
            }
            $value = $cityList[$country][$city];
        } elseif ($key == 'email') {
            $value = strtolower($value);
            $regexp = '/^[a-z0-9!#$%&\'*+\/\=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/\=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i';
            if (!preg_match($regexp, $value)) {
                throw new RinkaAsiException(
                    'Specified email is not valid: "' . $value . '"',
                    RinkaAsiException::E_VALUES
                );
            }
        } elseif ($key == 'phone') {
            if (!is_numeric(str_replace(array('+', '-', ' '), '', $value)) || strlen($value) < 5) {
                throw new RinkaAsiException(
                    'Specified phone is not valid: "' . $value . '"',
                    RinkaAsiException::E_VALUES
                );
            }

        }
        return $value;
    }

    /**
     * Adds one or more images to the entry. If some images were added before, they will be merged
     * with the new one(s).
     * Example of use:
     * $Entry->addImage('http://example.com/images/1.jpg');
     * $Entry->addImage(array('http://example.com/images/2.jpg', 'http://example.com/images/3.jpg'));
     *      // entry will contain all 3 images
     *
     * @param mixed $url        string URL of image or array of string URLs of images. Should be absolute URL
     * @return
     */
    public function addImage($url) {
        $this->images = array_merge($this->images, (array) $url);
    }

    /**
     * Sets publish date - date, when ad will become visible. If $date argument is invalid or
     * $date argument is not passed, publish date will be set to todays date.
     *
     * @param string $date [optional]      any string, recognizable by strtotime()
     * @return
     */
    public function setPublishDate($date = false) {
        $time = strtotime($date);
        if ($time === false) {
            $this->publishDate = date('Y-m-d H:i:s', time()); // today
        } else {
            $this->publishDate = date('Y-m-d H:i:s', $time);
        }
    }

    /**
     * Sets date, until when the ad should be published. If $date argument is invalid or
     * $date argument is not passed, publish date will be cleared and default from remote server used.
     *
     * @param string $date [optional]      any string, recognizable by strtotime()
     * @return
     */
    public function setPublishUntil($date) {
        $time = strtotime($date);
        if ($time === false) {
            $this->publishUntil = null;                     // just clear it
        } else {
            $this->publishUntil = date('Y-m-d H:i:s', $time);
        }
    }

    /**
     * Sets source url - url address or source announcement.
     *
     * @param string $url [optional]      any string, starting with http
     * @return
     */
    public function setSourceUrl($url = '') {
        $validUrl = (bool)preg_match("/^(http|https):\/\//i", $url);
        if ($validUrl) {
            $this->sourceUrl = $url;
        }
    }


    public function hasField($name) {
        return $this->Category->hasField($name);
    }
    public function getField($name) {
        return $this->Category->getField($name);
    }
    public function getAllFields() {
        return $this->Category->getAllFields();
    }

    /**
     * Sets specified field value.
     *
     * @param string    $name           name of field, as defined in category fields structure XML
     * @param mixed     $value          value of field's first (and usually only) "spot"
     * @param mixed     $value, ...     values of fields second, third, ... "spots", only needed when
     *                                  field has several values,
     *                                  eg. price and currency, number and unit etc.
     *                                  Example:
     *                                  <code>
     *                                  setFieldValue('simple', 'value');       // just 1 value
     *                                  setFieldValue('price', 699, 'ltl');     // more than 1 value
     *                                  </code>
     * @return
     */
    public function setFieldValue() {
        $value = func_get_args();
        if (count($value) < 2) {
            throw new RinkaAsiException(
                '2 or more arguments must be passed to setFieldValue method',
                RinkaAsiException::E_VALUES
            );
        }
        $name = array_shift($value);
        $this->setFieldValueArray($name, $value);
    }

    /**
     * Same as setFieldValue, but takes all values as array by $valueList argument
     *
     * @param string $name          name of field, as defined in category fields structure XML
     * @param array  $valueList     array of values, usually of length 1
     *                              more than 1 if field has several values, eg. price and currency
     *                              Example:
     *                              <code>
     *                              setFieldValue('simple', array('value'));       // just 1 value
     *                              setFieldValue('price', array(699, 'ltl'));     // more than 1 value
     *                              </code>
     * @return
     */
    public function setFieldValueArray($name, array $valueList) {
        if (!is_array($valueList)) {
            $valueList = array($valueList);
        }
        $Field = $this->getField($name);
        $FieldValue = new RinkaAsiFieldValue($this->DomDocument, $Field, $valueList);
        $this->fieldValues[$name] = $FieldValue;
    }

    public function setSourceBase($sourceBase) {
        $this->sourceBase = $sourceBase;
    }

    /**
     *
     * @param string $ip
     */
    public function setUserIp($ip) {
        $this->userIp = $ip;
    }

    public function setSkipContacts($skipContacts)
    {
        $this->skipContacts = $skipContacts;
    }

    public function getSkipContacts()
    {
        return $this->skipContacts;
    }

    /**
     * Validates entry before submission.
     *
     * @throws RinkaAsiException
     */
    public function validate() {
        if (!$this->skipContacts && empty($this->contacts['phone']) && empty($this->contacts['email'])) {
            throw new RinkaAsiException(
                "At least one phone number, email address",
                RinkaAsiException::E_MISSING_VALUES
            );
        }

        foreach ($this->Category->getAllFields() as $Field) {
            if ($Field->isRequired() && !isset($this->fieldValues[$Field->getName()])) {
                throw new RinkaAsiException(
                    'Field is required. ' . implode('/', $this->Category->getCategoryArray()) . ': ' . $Field->getName()
                );
            }
        }
    }
}






