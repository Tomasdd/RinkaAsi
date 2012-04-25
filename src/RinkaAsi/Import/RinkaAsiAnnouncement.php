<?php
class RinkaAsiAnnouncement {

    protected $id          = null;
    protected $category    = null;
    protected $publishDate = null;
    protected $url         = null;
    protected $imageList   = array();
    protected $fieldList   = array();
    protected $description = null;
    protected $title = null;
    protected $previewImage = null;

    /**
     *
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @param array $category
     */
    public function setCategory($category) {
        $this->category = $category;
        return $this;
    }

    /**
     *
     * @param string $publishDate
     */
    public function setPublishDate($publishDate) {
        $this->publishDate = $publishDate;
        return $this;
    }

    /**
     *
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    /**
     *
     * @param array $imageList
     */
    public function setImageList(array $imageList) {
        $this->imageList = $imageList;
        return $this;
    }

    /**
     *
     * @param array $fieldList
     */
    public function setFieldList(array $fieldList) {
        $this->fieldList = $fieldList;
        return $this;
    }

    /**
     *
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
    *
    * @param string $title
    */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     *
     * @param string $previewImage
     */
    public function setPreviewImage($previewImage) {
        $this->previewImage = $previewImage;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     *
     * @return array
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     *
     * @return string
     */
    public function getPublishDate() {
        return $this->publishDate;
    }

    /**
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     *
     * @return array
     */
    public function getImageList() {
        return $this->imageList;
    }

    /**
     *
     * @return array
     */
    public function getFieldList() {
        return $this->fieldList;
    }

    /**
     *
     * @return string
     */
    public function getField($title) {
        foreach ($this->fieldList as $item) {
            if ($item['title'] == $title) {
                return $item['value'];
            }
        }
        return null;
    }
    /**
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
    /**
    *
    * @return string
    */
    public function getTitle() {
        return $this->title;
    }

    /**
     *
     * @return string
     */
    public function getPreviewImage() {
        return $this->previewImage;
    }

}