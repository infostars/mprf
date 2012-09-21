<?php
namespace mpr\io;

class fileWriter
{
    private $resource;

    protected function getResource()
    {
        return $this->resource;
    }

    public function __construct($filename, $context = null)
    {
        if(isset($context)) {
            $this->resource = fopen($filename, 'w', null, $context);
        } else {
            $this->resource = fopen($filename, 'w');
        }
    }

    public function write($data)
    {
        fwrite($this->resource, $data);
    }

    public function writeLn($string)
    {
        $this->write("{$string}\n");
    }

    public function close()
    {
        fclose($this->resource);
    }
}