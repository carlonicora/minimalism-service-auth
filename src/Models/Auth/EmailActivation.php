<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ValidateActivation;
use Exception;

class EmailActivation extends AbstractAuthWebModel
{

    /**
     * @param PositionedParameter $email
     * @param PositionedParameter $code
     * @param PositionedParameter $client_id
     * @param PositionedParameter $state
     * @param PositionedParameter|null $source
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        PositionedParameter $email,
        PositionedParameter $code,
        PositionedParameter $client_id,
        PositionedParameter $state,
        ?PositionedParameter $source=null,
    ): HttpCode
    {
        $this->view = Views::Activation->getViewFileName();

        $this->auth->setEmail($email->getValue());
        $this->auth->setClientId($client_id->getValue());
        $this->auth->setState($state->getValue());

        if ($source !== null){
            $this->auth->setSource($source->getValue());
        }

        $this->document->meta->add(name: 'code', value: $code->getValue());

        $this->addFormAction(ValidateActivation::class);

        return HttpCode::Ok;
    }
}