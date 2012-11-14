<?php
namespace mpr;

/**
 * LIFO class
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class lifo
{

    /**
     * Output fifo filepath
     *
     * @var string
     */
    protected $output_filepath;

    /**
     * Input fifo filepath
     *
     * @var string
     */
    protected $input_filepath;

    /**
     * Input file handle
     *
     * @var resource
     */
    protected $input_handle;

    /**
     * Output file handle
     *
     * @var resource
     */
    protected $output_handle;

    /**
     * Set output filepath
     *
     * @param string $output_lifo_filepath
     */
    public function setOutputFile($output_lifo_filepath)
    {
        $this->checkFilepath($output_lifo_filepath);
        $this->output_filepath = $output_lifo_filepath;
    }

    /**
     * Check filepath
     *
     * @param string $filepath
     * @return bool result
     */
    protected function checkFilepath($filepath)
    {
        if(!file_exists($filepath)) {
            posix_mkfifo($filepath, 0777);
        }
        return true;
    }

    /**
     * Set input filepath
     *
     * @param string $input_lifo_filepath
     */
    public function setInputFile($input_lifo_filepath)
    {
        $this->checkFilepath($input_lifo_filepath);
        $this->input_filepath = $input_lifo_filepath;
    }

    /**
     * Send message to output lifo
     *
     * @param string $string string to send
     * @return int bytes sent
     */
    public function send($string)
    {
        $this->connectOutput();
        $string = preg_replace("/[\n]+/", "<br \/>", $string);
        $bytes = fwrite($this->output_handle, $string . "\n");
        $this->disconnectOutput();
        return $bytes;
    }

    /**
     * Read from lifo
     *
     * @param int $bytes Bytes count to read
     * @return string Result
     */
    public function read($bytes)
    {
        $this->connectInput();
        $content = fread($this->input_handle, $bytes);
        $this->disconnectInput();
        return $content;
    }

    /**
     * Connect to input lifo
     */
    protected function connectInput()
    {
        $this->input_handle = fopen($this->input_filepath, 'r');
    }

    /**
     * Disconnect from input lifo
     */
    protected function disconnectInput()
    {
        if(is_resource($this->input_handle)) {
            fclose($this->input_handle);
        }
        unset($this->input_handle);
    }

    /**
     * Connect to output lifo
     */
    protected function connectOutput()
    {
        $this->output_handle = fopen($this->output_filepath, 'w');
    }

    /**
     * Disconnect from output lifo
     */
    protected function disconnectOutput()
    {
        if(is_resource($this->output_handle)) {
            fclose($this->output_handle);
        }
        unset($this->output_handle);
    }

}