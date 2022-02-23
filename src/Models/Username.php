<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use Exception;

class Username extends AbstractAuthWebModel
{
    /**
     * @param EncrypterInterface $encrypter
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        EncrypterInterface $encrypter,
    ): HttpCode
    {
        $this->view = 'auth/username';

        $user = $this->auth->getAuthenticationTable()->authenticateById($this->auth->getUserId());

        $userResource = new ResourceObject(
            type: 'user',
            id: $encrypter->encryptId($user->getId()),
        );
        $userResource->attributes->add(
            name: 'username',
            value: $user->getUsername(),
        );

        if ($user->isSocialLogin()){
            $this->document->meta->add(name: 'isSocialLogin', value: true);
        }

        $this->document->addResource(
            resource: $userResource,
        );

        return HttpCode::Ok;
    }

    /**
     * @param string $username
     * @param string|null $password
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        string $username,
        ?string $password=null,
    ): HttpCode
    {
        $user = $this->auth->getAuthenticationTable()->authenticateById($this->auth->getUserId());

        if ($username !== $user->getUsername()){
            $this->auth->getAuthenticationTable()->updateUsername($user->getId(), $username);
        }

        if (!empty($password)){
            $this->auth->getAuthenticationTable()->updatePassword($user->getId(), password_hash($password, PASSWORD_BCRYPT));
        }

        $this->addCorrectRedirection();

        return HttpCode::Ok;
    }
}