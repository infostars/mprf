<?php

namespace mpr\net;

use \mpr\net\curl;

/**
 * Curl driver for HTTP Request class
 *
 * @author GreeveX <greevex@gmail.com>
 */
class multiCurl
{
    private $multi_curl;
    private $callback = null;

    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    public function __construct()
    {
        $this->multi_curl = curl_multi_init();
        $this->callback = array($this, 'callback');
    }

    public function callback($output, $info)
    {
        echo ".";
    }

    /**
     * Add curl to multicurl
     *
     * @param curl $curl
     */
    public function add($curl)
    {
        curl_multi_add_handle($this->multi_curl, $curl);
    }

    public function execute()
    {
        do {
            while (($execrun = curl_multi_exec($this->multi_curl, $running)) == CURLM_CALL_MULTI_PERFORM) {
                usleep(50);
            }
            if ($execrun != CURLM_OK) {
                break;
            }
            while ($done = curl_multi_info_read($this->multi_curl)) {

                $output = curl_multi_getcontent($done['handle']);
                $info = curl_getinfo($done['handle']);

                call_user_func_array($this->callback, array($output, $info));
                curl_multi_remove_handle($this->multi_curl, $done['handle']);
            }

            if ($running) {
                curl_multi_select($this->multi_curl, 10);
            }
        } while ($running);
        curl_multi_close($this->multi_curl);
    }
}