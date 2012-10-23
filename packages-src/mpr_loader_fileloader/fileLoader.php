<?php
namespace mpr\loader;

use \mpr\debug\log;
use \mpr\config;

/**
 * fileLoader class
 *
 * Searching and loading files
 * Can get contents and require
 * Also can load json files
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class fileLoader
{

    /**
     * Search file and load it
     *
     * @static
     * @param string $file File path
     * @return mixed Result
     * @throws \Exception
     */
    public static function load($file)
    {
        $filepath = self::search($file);
        if($filepath == null) {
            throw new \Exception("Unable to load file {$file} in {$filepath}!");
        }
        log::put("Loading: {$filepath}", config::getPackageName(__CLASS__));
        return require_once $filepath;
    }

    /**
     * Load json from file
     *
     * @static
     * @param string $file File to load
     * @param bool $as_array Parse json as array
     * @return mixed|null Result
     * @throws \Exception
     */
    public static function loadJson($file, $as_array = false)
    {
        $filepath = self::search($file);
        if($filepath == null) {
            throw new \Exception("Unable to load file {$file} in {$filepath}!");
        }
        log::put("Loading json: {$filepath}", config::getPackageName(__CLASS__));
        return json_decode(file_get_contents($filepath), $as_array);
    }

    /**
     * Search file
     *
     * @static
     * @param string $file Search file path
     * @return string|null Result
     */
    protected static function search($file)
    {
        log::put("Searching file: {$file}", config::getPackageName(__CLASS__));
        return file_exists($file) ? $file : null;
    }
}