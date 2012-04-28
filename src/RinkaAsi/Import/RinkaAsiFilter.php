<?php
class RinkaAsiFilter
{

    protected $id = null;
    protected $category = array();
    protected $cities = array();
    protected $orders = array();
    protected $pagingLimit = null;
    protected $pagingPage = null;
    protected $skipCount = false;
    protected $onlyWithImages = false;
    protected $fulltextSearch = null;

    const ORDER_BY_SITE = 'site';
    const ORDER_BY_DATE = 'date';
    const ORDER_BY_PRICE = 'price';
    const ORDER_DESC = 'desc';
    const ORDER_ASC = 'asc';

    /**
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @param array $category
     */
    public function setCategory($category)
    {
        if (is_array($category)) {
            $this->category = $category;
        } else {
            $this->category = explode('/', $category);
        }
        return $this;
    }

    /**
     * Set full text search
     *
     * @param string $fulltextSearch
     */
    public function setFulltextSearch($fulltextSearch)
    {
        $this->fulltextSearch = $fulltextSearch;
    }

    /**
     *
     * @param array $citiesList     array of 2 element array (country, city)
     */
    public function setCities($citiesList)
    {
        if ($citiesList === null) {
            $this->cities = null;
        } else {
            $this->cities = array();
            foreach ($citiesList as $city) {
                $this->addCity($city);
            }
        }
        return $this;
    }

    public function addCity($city)
    {
        if (!is_array($city) || count($city) !== 2) {
            throw new Exception('Each city should be array of 2 elements');
        }
        if ($this->cities === null) {
            $this->cities = array();
        }
        $this->cities[] = $city;
    }

    /**
     * Available orders:
     *   Order By:
     *      RinkaAsiFilter::ORDER_BY_SITE
     *      RinkaAsiFilter::ORDER_BY_PRICE
     *      RinkaAsiFilter::ORDER_BY_DATE
     *
     *   ASC/DESC:
     *      RinkaAsiFilter::ORDER_DESC
     *      RinkaAsiFilter::ORDER_ASC
     *
     *
     *   An example:
     *       array(
     *          RinkaAsiFilter::ORDER_BY_SITE  => 'randomwebsite.tld',
     *          RinkaAsiFilter::ORDER_BY_DATE  => RinkaAsiFilter::ORDER_DESC,
     *          RinkaAsiFilter::ORDER_BY_PRICE => RinkaAsiFilter::ORDER_ASC,
     *       );
     *
     * @param array $orders
     */
    public function setOrders(array $orders)
    {
        $this->orders = array();
        foreach ($orders as $key => $value) {
            $this->orders[] = $key . '-' . $value;
        }
        return $this;
    }

    /**
     *
     * @param int $pagingLimit
     */
    public function setPagingLimit($pagingLimit)
    {
        $this->pagingLimit = $pagingLimit;
        return $this;
    }

    /**
     *
     * @param int $pagingPage
     */
    public function setPagingPage($pagingPage)
    {
        $this->pagingPage = $pagingPage;
        return $this;
    }

    /**
     *
     * @param boolean $skipCount
     *
     * @return RinkaAsiFilter
     */
    public function setSkipCount($skipCount)
    {
        $this->skipCount = $skipCount;
        return $this;
    }

    /**
     *
     * @param boolean $onlyWithImages
     *
     * @return RinkaAsiFilter
     */
    public function setOnlyWithImages($onlyWithImages)
    {
        $this->onlyWithImages = $onlyWithImages;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return array
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     *
     * @return array
     */
    public function getCities()
    {
        return $this->cities;
    }

    public function getCity()
    {
        return is_array($this->cities) && count($this->cities) > 0 ? reset($this->cities) : null;
    }

    /**
     *
     * @return array
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     *
     * @return int
     */
    public function getPagingLimit()
    {
        return $this->pagingLimit;
    }

    /**
     *
     * @return int
     */
    public function getPagingPage()
    {
        return $this->pagingPage;
    }

    /**
     *
     * @return boolean
     */
    public function getSkipCount()
    {
        return $this->skipCount;
    }

    /**
     *
     * @return boolean
     */
    public function getOnlyWithImages()
    {
        return $this->onlyWithImages;
    }

    /**
     * @return string
     */
    public function getFulltextSearch()
    {
        return $this->fulltextSearch;
    }

    /**
     * Returns an array which contains all filter values
     *
     * @return array
     */
    public function toQueryParams(RinkaAsi $RinkaAsi)
    {
        $categoryKey = $this->getCategoryKey($RinkaAsi, $this->category);
        return array(
            'id'               => $this->id,
            'category'         => $categoryKey,
            'type'             => ($categoryKey === null && !empty($this->category)) ? current($this->category) : null,
            'cities'           => $this->cities === null ? null : $this->getCityKeys($RinkaAsi, $this->cities),
            'fulltext_search'  => $this->fulltextSearch,
            'orders'           => $this->orders,
            'paging'           => array(
                'limit'       => $this->pagingLimit,
                'page'        => $this->pagingPage,
                'skipCount'   => $this->skipCount,
            ),
            'onlyWithImages'   => $this->onlyWithImages ? '1' : '',
        );
    }

    protected function getCategoryKey(RinkaAsi $RinkaAsi, array $categoryPath)
    {
        if (empty($categoryPath)) {
            return null;
        }
        $categories = $RinkaAsi->getCategories();
        return $categories->getCategory($categoryPath)->getKey();
    }

    protected function getCityKeys(RinkaAsi $RinkaAsi, array $cityList)
    {
        $keys = array();
        foreach ($cityList as $city) {
            $keys[] = $this->getCityKey($RinkaAsi, $city);
        }
        return $keys;
    }

    protected function getCityKey(RinkaAsi $RinkaAsi, array $cityPath)
    {
        if (empty($cityPath)) {
            return null;
        }
        $categories = $RinkaAsi->getCategories();
        return $categories->getCityKey($cityPath);
    }
}