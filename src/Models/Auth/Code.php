<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ResendCode;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ValidateCode;
use Exception;

class Code extends AbstractAuthWebModel
{
    /**
     * @param PositionedEncryptedParameter|null $userId
     * @param PositionedParameter|null $code
     * @param PositionedParameter|null $clientId
     * @param PositionedParameter|null $state
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        ?PositionedEncryptedParameter $userId=null,
        ?PositionedParameter $code=null,
        ?PositionedParameter $clientId=null,
        ?PositionedParameter $state=null,
    ): HttpCode
    {
        if ($userId !== null && $clientId !== null && $state !== null && $code !== null){
            $this->auth->setUserId($userId->getValue());
            $this->auth->setClientId($clientId->getValue());
            $this->auth->setState($state->getValue());

            $this->document->meta->add(name: 'code', value: $code->getValue());
        }
        $this->view =  Views::Code->getViewFileName();

        $this->addFormAction(modelClass: ValidateCode::class);
        $this->addFormAction(modelClass: ResendCode::class, linkName: 'resendCodeAction');

        return HttpCode::Ok;
    }
}