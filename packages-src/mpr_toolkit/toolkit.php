<?php
namespace mpr;

use \mpr\pattern\singleton;
use \mpr\io\input;
use \mpr\io\output;

/**
 * toolkit - extendable toolkit
 *
 * @author GreeveX <greevex@gmail.com>
 */
class toolkit
extends singleton
{

    /**
     * Instance of input object
     *
     * @return \mpr\io\input
     */
    public function getInput()
    {
        static $input;
        if($input == null) {
            $input = new input();
        }
        return $input;
    }

    /**
     * Instance of output object
     *
     * @return \mpr\io\output
     */
    public function getOutput()
    {
        static $output;
        if($output == null) {
            $output = new output();
        }
        return $output;
    }

}