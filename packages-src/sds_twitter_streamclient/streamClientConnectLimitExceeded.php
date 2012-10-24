<?php

namespace sds\twitter;

/**
 * @author greevex
 * @date: 10/24/12 1:44 PM
 */
class streamClientConnectLimitExceeded
extends \Exception
{
    /**
     * Construct the exception.
     *
     * @note The message is NOT binary safe
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code.
     * @param \Exception $previous The previous exception used for the exception chaining. Since 5.3.0
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
