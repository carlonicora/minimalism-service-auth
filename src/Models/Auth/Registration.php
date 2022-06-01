<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ValidateRegistration;
use Exception;

class Registration extends AbstractAuthWebModel
{
    /**
     * @param string $email
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        string $email,
    ): HttpCode
    {
        $this->view =  Views::Registration->getViewFileName();

        $this->document->meta->add(name: 'email', value: $email);
        $this->addFormAction(ValidateRegistration::class);

        return HttpCode::Ok;
    }
}