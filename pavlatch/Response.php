<?php

namespace pavlatch;

class Response
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var string|null
     */
    private $error;

    public function __construct(?string $message = null, int $code = 200, ?string $error = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->error = $error;
    }

    public function __toString(): string
    {
        header('Content-Type: application/json');
        http_response_code($this->code);

        return '{' .
            '"code": ' . $this->code . ',' .
            '"message": "' . $this->message . '",' .
            '"error": "' . $this->error . '"' .
            '}';
    }
}
