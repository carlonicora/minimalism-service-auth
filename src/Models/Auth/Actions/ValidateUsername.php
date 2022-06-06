<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Factories\UsernameFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Authorisation;
use Exception;

class ValidateUsername extends AbstractAuthActionModel
{
    /**
     * @param string $username
     * @param string|null $password
     * @return HttpCode
     * @throws Exception
     */
    public function patch(
        string $username,
        ?string $password=null,
    ): HttpCode
    {
        $username = UsernameFactory::standardiseUsername($username);
        $user = $this->authenticator->authenticateById($this->auth->getUserId());

        if ($username !== $user->getUsername()){
            $this->authenticator->updateUsername($user->getId(), $username);
        }

        if ($password !== null && $password !== ''){
            $this->authenticator->updatePassword($user->getId(), password_hash($password, PASSWORD_BCRYPT));
        }

        $this->addRedirection(Authorisation::class);

        return HttpCode::Accepted;
    }
}