<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use Exception;

class SocialError extends AbstractAuthWebModel
{
    /**
     * @param string $error
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        string $error,
    ): HttpCode
    {
        $this->view = Views::SocialError->getViewFileName();

        $this->document->meta->add(name:'error', value: $error);

        return HttpCode::Ok;
    }
}