<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\ResetInitialised;
use Exception;

class InitialiseReset extends AbstractAuthActionModel
{
    /**
     * @param string $email
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        string $email,
    ): HttpCode
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ExceptionFactory::EmailInvalid->create();
        }

        try {
            $user = $this->authenticator->authenticateByEmail($email);
        } catch (Exception) {
            throw ExceptionFactory::EmailNotFound->create();
        }

        $this->auth->sendForgotEmail($user);
        $this->auth->setEmail($email);

        $this->addRedirection(ResetInitialised::class);

        return HttpCode::Accepted;
    }
}