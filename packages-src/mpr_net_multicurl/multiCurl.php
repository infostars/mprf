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
    /**
     * curl_multi resource instance
     *
     * @var resource
     */
    private $multi_curl;

    /**
     * Callback callable
     *
     * @var callable
     */
    private $callback;

    /**
     * Set callback on success
     *
     * @param callable $callback
     * @return callable
     */
    public function setCallback($callback)
    {
        return $this->callback = $callback;
    }

    /**
     * Construct new object of multiCurl
     */
    public function __construct()
    {
        $this->multi_curl = curl_multi_init();
        $this->setCallback([$this, 'callback']);
    }

    /**
     * Example of callback function
     *
     * @param string $output Curl content result
     * @param array $info Meta info
     * @return bool
     */
    public function callback($output, $info)
    {
        echo json_encode(
            [
                'output' => $output,
                'info'   => $info
            ]
        );

        return true;
    }

    /**
     * Add curl to multicurl
     *
     * @param curl $curl
     * @return int
     */
    public function add($curl)
    {
        return curl_multi_add_handle($this->multi_curl, $curl);
    }

    /**
     * Execute all curl object added in multiCurl
     *
     * @return bool
     */
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

        return true;
    }
}