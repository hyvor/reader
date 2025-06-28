<?php

namespace App\Service\Fetch\Exception;

class UnexpectedStatusCodeException extends \Exception
{
    public function __construct(private readonly int $httpCode, string $message = '')
    {
        if ($message === '') {
            $message = "Unexpected HTTP status code: {$httpCode}";
        }
        parent::__construct($message, $httpCode);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
} 