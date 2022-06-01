<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ValidatePassword;
use Exception;

class Password extends AbstractAuthWebModel
{
    /**
     * @return HttpCode
     * @throws Exception
     */
    public function get(
    ): HttpCode
    {
        $this->view = Views::Password->getViewFileName();

        $this->addFormAction(modelClass: ValidatePassword::class);

        return HttpCode::Ok;
    }
}