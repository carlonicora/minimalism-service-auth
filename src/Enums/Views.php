<?php
namespace CarloNicora\Minimalism\Services\Auth\Enums;

enum Views
{
    case Index;
    case Registration;
    case Activation;
    case Code;
    case Password;
    case Username;
    case Authorisation;
    case SocialError;
    case Forgot;
    case ResetInitialised;
    case Reset;
    case EmailLoginCode;
    case EmailActivationCode;
    case EmailForgotPassword;

    /**
     * @return string
     */
    public function getViewFileName(
    ): string
    {
        if (in_array($this, [self::EmailLoginCode, self::EmailActivationCode, self::EmailForgotPassword])){
            return 'Auth' . DIRECTORY_SEPARATOR . 'Emails' . DIRECTORY_SEPARATOR . substr($this->name, 5);
        }

        return 'Auth' . DIRECTORY_SEPARATOR . $this->name;
    }
}