<?php

namespace JosephAjibodu\Paystack;

use JosephAjibodu\Paystack\Exceptions\AuthenticationException;
use JosephAjibodu\Paystack\Exceptions\InvalidRequestException;
use JosephAjibodu\Paystack\Exceptions\PaystackException;
use JosephAjibodu\Paystack\Exceptions\RateLimitException;

class ErrorHandler
{
    /**
     * @throws RateLimitException
     * @throws InvalidRequestException
     * @throws AuthenticationException
     * @throws PaystackException
     */
    public static function handleApiError($response)
    {
        $httpStatus = $response['status'] ?? null;
        $httpBody = $response['body'] ?? null;
        $jsonBody = json_decode($httpBody, true);

        $errorType = $jsonBody['error']['type'] ?? null;
        $errorCode = $jsonBody['error']['code'] ?? null;
        $errorMessage = $jsonBody['error']['message'] ?? 'Unknown error occurred';

        throw match ($httpStatus) {
            400 => new InvalidRequestException($errorMessage, $errorCode, $errorType, $httpStatus, $httpBody),
            401 => new AuthenticationException($errorMessage, $errorCode, $errorType, $httpStatus, $httpBody),
            429 => new RateLimitException($errorMessage, $errorCode, $errorType, $httpStatus, $httpBody),
            default => new PaystackException($errorMessage, $errorCode, $errorType, $httpStatus, $httpBody),
        };
    }
}