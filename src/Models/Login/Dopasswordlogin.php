<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Login;

use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Dopasswordlogin extends AbstractAuthWebModel
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
        string $password
    ): int
    {
        if (($user = $auth->getAuthenticationTable()->authenticateById($userId->getValue())) === null){
            throw new RuntimeException('Invalid email or password', 401);
        }

        if (!$this->decryptPassword($password, $user['password'])){
            throw new RuntimeException('Invalid email or password', 401);
        }

        $auth->setUserId($userId->getValue());

        $this->document->meta->add(
            'redirection',
            $path->getUrl() . 'auth'
        );

        return 200;
    }

    /**
     * Verifies if a password matches its hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    private function decryptPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}