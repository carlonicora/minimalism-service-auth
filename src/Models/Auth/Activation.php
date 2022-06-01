<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ValidateActivation;
use Exception;

class Activation extends AbstractAuthWebModel
{
    /**
     * @return HttpCode
     * @throws Exception
     */
    public function get(
    ): HttpCode
    {
        $this->view = Views::Activation->getViewFileName();

        $this->addFormAction(modelClass: ValidateActivation::class);

        return HttpCode::Ok;
    }
}