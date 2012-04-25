<?php

/**
 * RinkaAsiCacheInterface
 */
interface RinkaAsiCacheInterface {
    /**
     * Load
     *
     * @param string $name
     *
     * @return mixed
     */
    public function load($name);

    /**
     * Save
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function save($name, $value);
}