<?php

require_once "RinkaAsiWebClientInterface.php";
require_once dirname(__FILE__) . "/../RinkaAsiException.php";

/**
 * RinkaAsiCurlWebClient
 */
class RinkaAsiCurlWebClient implements RinkaAsiWebClientInterface
{
    /**
     * Get
     *
     * @param string $url
     *
     * @return string
     *
     * @throws RinkaAsiException
     */
    public function get($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (curl_errno($ch) || floor($info['http_code'] / 100) != 2) {
            throw new RinkaAsiException(
                sprintf("Got response %s while getting contents from %s\n\nInfo:\n%s", $output, $url,
                    print_r($info, true)),
                RinkaAsiException::E_CONNECTION
            );
        }

        curl_close($ch);
        return $output;
    }

    /**
     * Post
     *
     * @param string $url
     * @param array  $postData
     *
     * @return string
     *
     * @throws RinkaAsiException
     */
    public function post($url, array $postData)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $output = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (curl_errno($ch) || floor($info['http_code'] / 100) != 2) {
            throw new RinkaAsiException(
                sprintf("Got response %s while getting contents from %s\n\nInfo:\n%s", $output, $url,
                    print_r($info, true)),
                RinkaAsiException::E_CONNECTION
            );
        }

        curl_close($ch);
        return $output;
    }
}