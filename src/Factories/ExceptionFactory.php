<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;

enum ExceptionFactory: int
{
    private const SERVICE_IDENTIFIER=10010000;

    case WrongPassword=1;
    case CodeInvalidOrExpired=2;
    case PasswordOrCodeMising=3;
    case MissingUserInformation=4;
    case FacebookAccountMissingEmail=5;
    case AppleResponseStateMismatch=6;
    case AppleResponseMissingToken=7;
    case AppleIdNotFound=8;
    case AppleIdNotMatchingAccount=9;
    case GoogleAccountMissingEmail=10;

    /**
     * @return MinimalismException
     */
    public function create(
    ): MinimalismException
    {
        return match ($this) {
            self::WrongPassword => new MinimalismException(
                status: HttpCode::Unauthorized,
                message: 'wrong password',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::CodeInvalidOrExpired => new MinimalismException(
                status: HttpCode::Unauthorized,
                message: 'code invalid or expired',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::PasswordOrCodeMising => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'password or code missing',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::MissingUserInformation => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'user information missing',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            default =>  new MinimalismException(
                status: HttpCode::InternalServerError,
                message: $this->name,
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
        };
    }
}