<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Factories\UsernameFactory;
use Exception;

class UsernameCheck extends AbstractAuthActionModel
{
    /**
     * @param string $username
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        string $username,
    ): HttpCode
    {
        $username = UsernameFactory::standardiseUsername($username);

        if ($this->auth->getUserId() === null){
            throw ExceptionFactory::MissingUserInformation->create();
        }

        $user = $this->authenticator->authenticateById($this->auth->getUserId());

        if ($user->getUsername() !== $username){
            try {
                /** @noinspection UnusedFunctionResultInspection */
                $this->authenticator->authenticateByUsername($username);
                throw ExceptionFactory::UsernameAlreadyInUse->create();
            } catch (Exception) {
            }
        }

        return HttpCode::NoContent;
    }
}