<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use Exception;

class Activation extends AbstractAuthWebModel
{
    /**
     * @param EncrypterInterface $encrypter
     * @param EncryptedParameter|null $userId
     * @param string|null $client_id
     * @param string|null $state
     * @param string|null $code
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        EncrypterInterface $encrypter,
        ?EncryptedParameter $userId=null,
        ?string $client_id=null,
        ?string $state=null,
        ?string $code=null,
    ): HttpCode
    {
        $this->view = 'code';

        if ($client_id !== null) {
            $this->auth->setClientId($client_id);
        }

        if ($state !== null) {
            $this->auth->setState($state);
        }

        if ($userId === null && $this->auth->getUserId() === null){
            throw ExceptionFactory::MissingUserInformation->create();
        }

        if ($userId !== null) {
            $user = $this->auth->getAuthenticationTable()->authenticateById($userId->getValue());
            $this->auth->setUserId($user->getId());
        } else {
            $user = $this->auth->getAuthenticationTable()->authenticateById($this->auth->getUserId());
        }

        $userResource = new ResourceObject(
            type: 'user',
            id: $encrypter->encryptId($user->getId()),
        );

        if ($code !== null) {
            $userResource->attributes->add(
                name: 'code',
                value: $code,
            );
        }

        $this->document->addResource(
            resource: $userResource,
        );

        return HttpCode::Ok;
    }
}