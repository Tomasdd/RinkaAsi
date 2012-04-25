<?php

interface RinkaAsiWebClientInterface {

    /**
     * Loads contents from specified URL
     *
     * @param string $url   URL to load
     * @return string       loaded contents
     */
    public function get($url);

    /**
     * Posts $postData to $url and returns contents
     *
     * @param string    $url        URL to post to
     * @param array     $postData   data to post
     * @return string               loaded contents
     */
    public function post($url, array $postData);
}
