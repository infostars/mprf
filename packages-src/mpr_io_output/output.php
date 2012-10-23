<?php
namespace mpr\io;

/**
 * Output
 *
 * @author GreeveX <greevex@gmail.com>
 */
class output
{

    /**
     * Opened output connection
     *
     * @var resource
     */
    private $outputResource;

    /**
     * Output buffering flag
     *
     * @var bool|null
     */
    private $outputBuffering = false;

    /**
     * Output type OUT_OUTPUT|OUT_STDOUT
     *
     * @var bool|int
     */
    private $outputType = false;

    /**
     * In-memory buffer
     *
     * @var string
     */
    private $buffer = "";

    /**
     * Output type php://output
     */
    const OUT_OUTPUT = 89;

    /**
     * Output type php://stdout
     */
    const OUT_STDOUT = 91;

    /**
     * Construct new object instance
     *
     * @param bool $buffering
     * @param int $type
     */
    public function __construct($buffering = false, $type = self::OUT_STDOUT)
    {
        $this->outputBuffering = $buffering;
        $this->outputType = in_array($type, [self::OUT_OUTPUT, self::OUT_STDOUT]) ? $type : self::OUT_STDOUT;
        $this->outputResource = fopen($this->outputType == self::OUT_STDOUT ? 'php://stdout' : 'php://output', 'w');
    }

    /**
     * Commit changes to output
     *
     * @return bool
     */
    public function commit()
    {
        if($this->outputBuffering) {
            fwrite($this->outputResource, $this->buffer);
            $this->buffer = "";
        }
        return true;
    }

    /**
     * Write data to output
     *
     * @param string $string
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
     * Write data on single line to output
     *
     * @param string $string
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

    /**
     * Set custom output resource
     *
     * @param resource $resource
     * @return bool
     */
    public function setOutputResource($resource)
    {
        if(is_resource($this->outputResource)) {
            fclose($this->outputResource);
        }
        $this->outputResource = $resource;
        return true;
    }

    /**
     * Get current output resource
     *
     * @return resource
     */
    public function getOutputResource()
    {
        return $this->outputResource;
    }

    /**
     * Close opened output resource
     *
     * @return bool
     */
    public function close()
    {
        $this->commit();
        if(is_resource($this->outputResource)) {
            fclose($this->outputResource);
        }
        return true;
    }

    /**
     * Destruct object
     */
    public function __destruct()
    {
        $this->close();
    }
}