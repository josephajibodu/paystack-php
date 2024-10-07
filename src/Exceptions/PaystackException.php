<?php

namespace JosephAjibodu\Paystack\Exceptions;

use Exception;

class PaystackException extends Exception
{
    protected string $errorType;

    protected int $errorCode;

    protected string $httpStatus;

    protected string $httpBody;

    public function __construct($message = '', $code = 0, $errorType = null, $httpStatus = null, $httpBody = null)
    {
        parent::__construct($message, $code);
        $this->errorType = $errorType;
        $this->httpStatus = $httpStatus;
        $this->httpBody = $httpBody;
    }

    public function getErrorType()
    {
        return $this->errorType;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function getHttpBody()
    {
        return $this->httpBody;
    }
}
