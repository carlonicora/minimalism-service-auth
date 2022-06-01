<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\IO\CodeIO;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\CompleteReset;
use Exception;

class Reset extends AbstractAuthWebModel
{
    /**
     * @param PositionedEncryptedParameter $userId
     * @param PositionedParameter $code
     * @param PositionedParameter $clientId
     * @param PositionedParameter $state
     * @param PositionedParameter|null $source
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        PositionedEncryptedParameter $userId,
        PositionedParameter $code,
        PositionedParameter $clientId,
        PositionedParameter $state,
        ?PositionedParameter $source=null,
    ): HttpCode
    {
        $this->auth->setUserId($userId->getValue());
        $this->auth->setClientId($clientId->getValue());
        $this->auth->setState($state->getValue());

        if ($source !== null){
            $this->auth->setSource($source->getValue());
        }

        $this->view = Views::Reset->getViewFileName();

        $user = $this->authenticator->authenticateById($userId->getValue());

        $isCodeValid = $this->objectFactory->create(CodeIO::class)->isCodeValid(code: $code->getValue(), userId: $user->getId());

        if ($isCodeValid){
            $this->document->meta->add(name: 'code', value: $code->getValue());
        } else {
            $this->document->meta->add(name: 'error', value: ExceptionFactory::CodeInvalidOrExpired->create()->getMessage());
        }

        $this->addFormAction(modelClass: CompleteReset::class,method: 'PATCH');

        return HttpCode::Ok;
    }
}