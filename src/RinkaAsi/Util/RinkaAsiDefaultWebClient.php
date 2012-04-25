<?php

require_once "RinkaAsiWebClientInterface.php";
require_once dirname(__FILE__) . "/../RinkaAsiException.php";

class RinkaAsiDefaultWebClient implements RinkaAsiWebClientInterface
{

    public function get($url)
    {
        return $this->request('GET', $url);
    }

    public function post($url, array $postData)
    {
        return $this->request('POST', $url, $postData, 30);
    }

    protected function request($method, $url, array $data = array(), $timeout = 10)
    {
        $urlParts = array_merge(array(
            'scheme' => 'http',
            'port'   => 80
        ), parse_url($url));

        if ($urlParts['scheme'] != 'http') {
            throw new RinkaAsiException(
                'Only http protocol is supported by RinkaAsiDefaultWebClient. Given URL: ' . $url,
                RinkaAsiException::E_CONFIGURATION
            );
        }
        $host = $urlParts['host'];
        $path = $urlParts['path'];
        $port = $urlParts['port'];
        $query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';

        $errno = null;
        $errstr = null;

        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            throw new RinkaAsiException('Cannot connect to ' . $host, RinkaAsiException::E_CONNECTION);
        }

        if (!empty($data) && $method == 'POST') {
            $postData = array();
            foreach ($data as $key => $value) {
                $postData[] = urlencode($key) . '=' . urlencode($value);
            }
            $post = implode('&', $postData);
        } else {
            $post = false;
        }

        $out = $method . " " . $path . $query . " HTTP/1.0\r\n";

        $out .= "Host: " . $host . "\r\n";
        if ($post) {
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-Length: " . strlen($post) . "\r\n";
        }
        $out .= "Connection: Close\r\nAccept-Encoding: identity\r\nUser-Agent: RinkaAsi\r\n\r\n";
        if ($post) {
            $out .= $post;
        }

        $content = '';

        fwrite($fp, $out);
        while (!feof($fp)) {
            $content .= fgets($fp, 8192);
        }
        fclose($fp);

        list($header, $content) = explode("\r\n\r\n", $content, 2);

        $firstLine = substr($header, 0, strpos($header, "\r\n"));
        $status = substr($firstLine, strpos($firstLine, " ") + 1);
        $statusCode = intval(substr($status, 0, strpos($status, " ")));

        if (floor($statusCode / 100) != 2) { // if not 2xx
            throw new RinkaAsiException(
                'Got status "' . $status . '" while getting contents from "' . $url . '"',
                RinkaAsiException::E_CONNECTION
            );
        }

        return $content;
    }
}
