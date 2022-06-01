<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;

class ResetInitialised extends AbstractAuthWebModel
{
    /**
     * @return HttpCode
     */
    public function get(
    ): HttpCode
    {
        $this->view = Views::ResetInitialised->getViewFileName();

        return HttpCode::Ok;
    }
}