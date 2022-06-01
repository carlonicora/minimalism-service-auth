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
    case TermsAndConditionsNotAccepted=11;
    case EmailInvalid=12;
    case MissingClientId=13;
    case MissingState=14;
    case UnauthorisedScopes=15;
    case EmailNotFound=16;
    case ClientIdInvalid=17;
    case UsernameAlreadyInUse=18;
    case InvalidDomain=19;

    /**
     * @return MinimalismException
     */
    public function create(
    ): MinimalismException
    {
        return match ($this) {
            self::WrongPassword => new MinimalismException(
                status: HttpCode::Unauthorized,
                message: 'Password is incorrect.',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::CodeInvalidOrExpired => new MinimalismException(
                status: HttpCode::Unauthorized,
                message: 'This code is invalid or has expired.',
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
            self::TermsAndConditionsNotAccepted => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'Terms and Condiditions have not been accepted',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::EmailInvalid => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'The email is not a valid email address',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::EmailNotFound => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'The email is not associated to a valid account',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::MissingClientId => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'The application configuration is missing',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::MissingState => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'The application configuration scope is missing',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::UnauthorisedScopes => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'No scopes have been authorised',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::ClientIdInvalid => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'The OAuth client_id is not recognised',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::UsernameAlreadyInUse => new MinimalismException(
                status: HttpCode::Conflict,
                message: 'The selected username is already in use',
                code: self::SERVICE_IDENTIFIER + $this->value,
            ),
            self::InvalidDomain => new MinimalismException(
                status: HttpCode::PreconditionFailed,
                message: 'The email belongs to a domain which is not supported. Please use a different email address.',
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