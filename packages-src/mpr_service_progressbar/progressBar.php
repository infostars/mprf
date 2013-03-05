<?php

namespace mpr\service;

use \mpr\toolkit;

/**
 * consoleTimer: Timer and progressbar for console applications
 *
 * @package system
 * @subpackage service
 * @version 0.7
 * @author GreeveX <greevex@gmail.com>
 * @author Yancharuk Alexander <yancharuk@infostars.ru>
 */
class progressBar
{
    /**
     * Total amount of data
     *
     * @var int
     */
    protected $data_counter = 0;

    /**
     * One percent of total amount of data
     *
     * @var float
     */
    protected $percent = 0;

    /**
     * The length of the progress bar to draw
     *
     * @var int
     */
    protected $pb_width = 60;

    /**
     * One percent of progress bar length
     *
     * @var float
     */
    protected $pb_percent = 0;

    /**
     * Time when progressbar starts
     *
     * @var float
     */
    protected $time_start = 0;

    /**
     * Elapsed time when progressbar starts
     *
     * @var float
     */
    protected $time_elapsed = 0;

    /**
     * What percentage of processing is completed
     *
     * @var float
     */
    protected $done = 0;

    /**
     * How many seconds are left to wait
     *
     * @var float
     */
    protected $time_estimated = 0;

    /**
     * Output progressbar format
     *
     * @var string
     */
    protected $output_format = '[{progress}] | {done}% | {speed} p/s | te {time} | r {ram}';

    /**
     * Construct method
     * Send how mush data in progressbar and progressbar width
     *
     * @param int $data_counter - the amount of data
     * @param int $pb_width - progressbar width
     */
    public function __construct($data_counter, $pb_width = 60)
    {
        stream_set_blocking(STDIN, false);

        $this->time_start = microtime(true);
        $this->data_counter = (int) max($data_counter, 1);
        $this->percent = $data_counter / 100;
        $this->speed = 0;
        $this->pb_width = $pb_width;
        $this->pb_percent = $pb_width / 100;
    }

    /**
     * Update progressbar for calculating elapsed time, done percent, speed and estimated time
     *
     * @param int $curr_element - how many items already passed
     * @return progressBar
     */
    public function update($curr_element)
    {
        $this->time_elapsed = microtime(true) - $this->time_start;
        $this->done = $curr_element / $this->percent;
        $this->speed = $curr_element / $this->time_elapsed;
        $this->time_estimated = ($this->data_counter - $curr_element) / $this->speed;

        return $this;
    }

    /**
     * Draw a progressbar in the console, there should be no line breaks between "frames"!
     * Before each new "frame" call an update to the latest information
     */
    public function draw()
    {
        if ($this->done < 100) {
            $position = floor($this->pb_percent * $this->done);
            $end = "\033[K\r";
        } else {
            $position = $this->pb_width;
            $end = "\033[K\n\033[?25h";
        }

        $space_length = $this->pb_width - $position;
        $bar = "\033[?25l" . str_repeat('=', $position) . '>';

        $space_length && $bar .= "\033[{$space_length}C";

        $search = array(
            '{progress}',
            '{done}',
            '{speed}',
            '{time}',
            '{ram}'
        );
        $replace = array(
            $bar,
            number_format($this->done, 2),
            number_format($this->speed, 2),
            date("i:s", $this->time_estimated),
            round(memory_get_usage(true)/1024/1024, 4)
        );
        $string = str_replace($search, $replace, $this->output_format) . $end;

        self::stdinCleanup();

        toolkit::getInstance()->getOutput()->write($string);
    }

    /**
     * Set output format for progressbar
     *
     * @param string $string
     */
    public function setOutputFormat($string)
    {
        $this->output_format = $string;
    }

    /**
     * Cleanup user input
     */
    private static function stdinCleanup()
    {
        if (!fgets(STDIN)) {
            return;
        }

        echo "\033[K\033[1A";
    }

}