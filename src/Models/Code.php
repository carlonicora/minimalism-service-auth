<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use Exception;

class Code extends AbstractAuthWebModel
{
    /**
     * @param PositionedEncryptedParameter $userId
     * @param PositionedParameter $code
     * @param PositionedParameter $clientId
     * @param PositionedParameter $state
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        PositionedEncryptedParameter $userId,
        PositionedParameter $code,
        PositionedParameter $clientId,
        PositionedParameter $state,
    ): HttpCode
    {
        $this->auth->setClientId($clientId->getValue());
        $this->auth->setState($state->getValue());
        $this->auth->setUserId($userId->getValue());

        $this->view = 'code';

        $this->document->meta->add(name: 'code', value: $code->getValue());

        $this->document->addResource(
            new ResourceObject(
                type: 'user',
                id: $userId->getEncryptedValue(),
            )
        );

        return HttpCode::Ok;
    }
}