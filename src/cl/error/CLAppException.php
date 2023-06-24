<?php

namespace cl\error;

class CLAppException extends \Exception
{
    public function __construct($message = "Access to requested resource denied", $code = 403, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
