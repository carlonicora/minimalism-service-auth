<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Activation;
use Exception;

class ValidateRegistration extends AbstractAuthActionModel
{
    /**
     * @param string $email
     * @param bool $acceptTnC
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        string $email,
        bool $acceptTnC,
    ): HttpCode
    {
        if ($acceptTnC === false){
            throw ExceptionFactory::TermsAndConditionsNotAccepted->create();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ExceptionFactory::EmailInvalid->create();
        }

        $this->auth->validateDomain($email);

        $this->auth->setEmail($email);

        $this->auth->sendActivationEmail($email);

        $this->document->meta->add(name: 'email', value: $email);

        $this->addRedirection(Activation::class);

        return HttpCode::Accepted;
    }
}