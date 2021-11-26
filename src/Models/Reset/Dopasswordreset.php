<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Reset;

use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Dopasswordreset extends AbstractAuthWebModel
{
    /**
     * @param Auth $auth
     * @param Path $path
     * @param EncryptedParameter $userId
     * @param string $password
     * @return int
     * @throws Exception
     */
    public function post(
        Auth $auth,
        Path $path,
        EncryptedParameter $userId,
        string $password,
    ): int
    {
        if ($auth->getAuthenticationTable()->authenticateById($userId->getValue()) === null) {
            throw new RuntimeException('Invalid email or password', 401);
        }

        $auth->getAuthenticationTable()->updatePassword($userId->getValue(), $password);

        $auth->setUserId($userId->getValue());

        $this->document->meta->add(
            'redirection',
            $path->getUrl() . 'auth'
        );

        return 200;
    }
}