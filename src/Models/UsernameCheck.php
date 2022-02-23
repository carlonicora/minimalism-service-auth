<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Services\Auth\Auth;
use Exception;

class UsernameCheck extends AbstractModel
{
    /**
     * @param Auth $auth
     * @param EncryptedParameter $userId
     * @param string $username
     * @return HttpCode
     */
    public function get(
        Auth $auth,
        EncryptedParameter $userId,
        string $username,
    ): HttpCode
    {
        try {
            $user = $auth->getAuthenticationTable()->authenticateByUsername($username);

            if ($user->getId() !== $userId->getValue()) {
                return HttpCode::Conflict;
            }
        } catch (Exception) {
        }

        return HttpCode::NoContent;
    }
}