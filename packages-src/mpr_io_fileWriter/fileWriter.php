<?php
namespace mpr\io;

/**
 * FileWriter package
 *
 * Wrapper for fopen, fwrite functions
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class fileWriter
{
    /**
     * Opened file resource
     *
     * @var resource
     */
    private $resource;

    /**
     * Get resource of opened file
     *
     * @return resource
     */
    protected function getResource()
    {
        return $this->resource;
    }

    /**
     * Construct new object
     *
     * @param string $filename Path to file
     * @param null $context Context
     */
    public function __construct($filename, $context = null)
    {
        if(isset($context)) {
            $this->resource = fopen($filename, 'w', null, $context);
        } else {
            $this->resource = fopen($filename, 'w');
        }
    }

    /**
     * Write data to file
     *
     * @param mixed $data
     * @return int Bytes written
     */
    public function write($data)
    {
        return fwrite($this->resource, $data);
    }

    /**
     * Write string line to file
     *
     * @param string $string
     * @return int Bytes written
     */
    public function writeLn($string)
    {
        return $this->write("{$string}\n");
    }

    /**
     * Close opened resource
     *
     * @return bool
     */
    public function close()
    {
        return fclose($this->resource);
    }
}