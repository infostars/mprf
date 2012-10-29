<?php
namespace mpr\helper;

use \mpr\pattern\singleton;

/**
 *
 */
class json
extends singleton
{

    /**
     * @param $data
     * @param $params
     */
    public function encode($data, $pretty = false, $raw_unicode = false)
    {

    }

    /**
     * @param $data
     * @param $options
     * @return string
     */
    public function encodeByOptions($data, $options)
    {
        return json_encode($data, $options);
    }

    /**
     * @param $string
     * @param bool $as_array
     */
    public function decode($string, $as_array = false)
    {

    }

    /**
     * @param $filepath
     * @param bool $as_array
     */
    public function decodeFile($filepath, $as_array = false)
    {

    }

}