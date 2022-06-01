<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Code;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Password;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Registration;
use Exception;

class ValidateAccount extends AbstractAuthActionModel
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
        if ($email === ''){
            throw ExceptionFactory::EmailInvalid->create();
        }

        try {
            $user = $this->authenticator->authenticateByEmail(strtolower($email));
            $this->auth->setUserId($user->getId());

            if (in_array($user->getPassword(), [null, ''], true)){
                $this->auth->sendCodeEmail($user);

                $this->addRedirection(Code::class);
            } else {
                $this->addRedirection(Password::class);
            }
        } catch (Exception) {
            $this->addRedirection(pageClass: Registration::class, parameters: ['email' => $email]);
        }

        return HttpCode::Accepted;
    }
}