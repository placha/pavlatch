<?php

namespace pavlatch\Exception;

use pavlatch\Response;
use Throwable;

class ServerException extends \Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): Response
    {
        return new Response(null, $this->getCode(), $this->getMessage());
    }
}
