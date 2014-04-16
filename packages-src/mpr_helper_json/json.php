<?php
namespace mpr\helper;

/**
 * Json package
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class json
{

    /**
     * @param      $data
     * @param bool $pretty
     * @param bool $raw_unicode
     * @return string
     */
    public static function encode($data, $pretty = false, $raw_unicode = false)
    {
        $options = 0;
        if($pretty) {
            $options += JSON_PRETTY_PRINT;
        }
        if($raw_unicode) {
            $options += JSON_UNESCAPED_UNICODE;
        }
        return json_encode($data, $options);
    }

    /**
     * @param       $data
     * @param array $options
     * @return string
     */
    public static function encodeByOptions($data, $options = [])
    {
        $options = array_sum($options);
        return json_encode($data, $options);
    }

    /**
     * @param      $string
     * @param bool $as_array
     * @return mixed
     */
    public static function decode($string, $as_array = false)
    {
        return json_decode($string, $as_array);
    }

    /**
     * @param      $filepath
     * @param bool $as_array
     * @return mixed
     */
    public static function decodeFromFile($filepath, $as_array = false)
    {
        $content = file_get_contents($filepath);
        return self::decode($content, $as_array);
    }

    /**
     * @param       $filepath
     * @param       $data
     * @param array $options
     * @return bool
     */
    public static function encodeToFile($filepath, $data, $options = [])
    {
        $content = self::encodeByOptions($data, $options);
        file_put_contents($filepath, $content);
        return true;
    }

}