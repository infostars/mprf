<?php
namespace mpr\io;

/**
 * Output
 *
 * @author GreeveX <greevex@gmail.com>
 */
class output
{

    private $outputResource;

    private $outputBuffering = false;

    private $outputType = false;

    private $buffer = "";

    const OUT_OUTPUT = 89;
    const OUT_STDOUT = 91;

    public function __construct($buffering = null, $type = self::OUT_STDOUT)
    {
        $this->outputBuffering = ($buffering == null ? false : $buffering);
        $this->outputType = in_array($type, [self::OUT_OUTPUT, self::OUT_STDOUT]) ? $type : self::OUT_STDOUT;
        $this->outputResource = fopen($this->outputType == self::OUT_STDOUT ? 'php://stdout' : 'php://output', 'w');

    }

    public function commit()
    {
        if($this->outputBuffering) {
            fwrite($this->outputResource, $this->buffer);
            $this->buffer = "";
        }
    }

    /**
     * @param $string
     * @return \mpr\io\output
     */
    public function write($string)
    {
        if($this->outputBuffering) {
            $this->buffer .= $string;
        } else {
            if(!is_resource($this->outputResource)) {
                throw new \Exception("Output resource is closed!");
            }
            fwrite($this->outputResource, $string);
        }
        return $this;
    }

    /**
     * @param $string
     * @return \mpr\io\output
     */
    public function writeLn($string)
    {
        $this->write($string . "\n");
        return $this;
    }

    /**
    * Outputs exception info
    *
    * @param \Exception $exception
    * @return \mpr\io\output
    */
    public function writeException($exception)
    {
        $this->write("EXCEPTION: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}" . "\n");
        return $this;
    }

    public function setOutputResource($resource)
    {
        if(is_resource($this->outputResource)) {
            fclose($this->outputResource);
        }
        $this->outputResource = $resource;
    }

    /**
     * @return resource
     */
    public function getOutputResource()
    {
        return $this->outputResource;
    }

    public function close()
    {
        $this->commit();
        if(is_resource($this->outputResource)) {
            fclose($this->outputResource);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}