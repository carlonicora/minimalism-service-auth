<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\InitialiseReset;
use Exception;

class ResetInitialised extends AbstractAuthWebModel
{
    /**
     * @return HttpCode
     * @throws Exception
     */
    public function get(
    ): HttpCode
    {
        $this->view = Views::ResetInitialised->getViewFileName();

        $this->document->meta->add(name: 'email', value: $this->auth->getEmail());
        $this->addFormAction(modelClass: InitialiseReset::class);

        return HttpCode::Ok;
    }
}