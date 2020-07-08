<?php
namespace CarloNicora\Minimalism\Services\Auth\Events;

use CarloNicora\Minimalism\Core\Events\Abstracts\AbstractErrorEvent;
use CarloNicora\Minimalism\Core\Events\Interfaces\EventInterface;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;

class AuthErrorEvents extends AbstractErrorEvent
{
    protected string $serviceName = 'auth';

    public static function INVALID_EMAIL_OR_PASSWORD() : EventInterface
    {
        return new self(
            1,
            ResponseInterface::HTTP_STATUS_401,
            'Invalid email or password'
        );
    }

    public static function ACCOUNT_NOT_ACTIVE(int $userId) : EventInterface
    {
        return new self(
            2,
            ResponseInterface::HTTP_STATUS_412,
            'The account (user id '  . $userId . ') has not been activated yet'
        );
    }

    public static function AUTH_CODE_EXPIRED() : EventInterface
    {
        return new self(
            3,
            ResponseInterface::HTTP_STATUS_412,
            'The authorization code is incorrect or expired'
        );
    }

    public static function AUTH_INTERFACE_NOT_CONFIGURED() : EventInterface
    {
        return new self(
            4,
            ResponseInterface::HTTP_STATUS_500,
            'The authorization interface is not configured'
        );
    }

    public static function INVALID_ACCOUNT() : EventInterface
    {
        return new self(
            5,
            ResponseInterface::HTTP_STATUS_401,
            'Could not find your account'
        );
    }
}