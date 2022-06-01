<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Authorisation;
use Exception;

class ValidatePassword extends AbstractAuthActionModel
{
    /**
     * @param string $password
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        string $password,
    ): HttpCode
    {
        if ($password === ''){
            throw ExceptionFactory::WrongPassword->create();
        }
        
        $user = $this->authenticator->authenticateById($this->auth->getUserId());

        if (!password_verify($password, $user->getPassword())){
            throw ExceptionFactory::WrongPassword->create();
        }

        $this->auth->setIsAuthenticated(true);

        $this->addRedirection(Authorisation::class);

        return HttpCode::Accepted;
    }
}