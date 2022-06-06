<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\UsernameCheck;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ValidateUsername;
use Exception;

class Username extends AbstractAuthWebModel
{
    /**
     * @return HttpCode
     * @throws Exception
     */
    public function get(
    ): HttpCode
    {
        $this->view = Views::Username->getViewFileName();

        $user = $this->authenticator->authenticateById($this->auth->getUserId());

        $this->document->meta->add(name: 'username', value: explode('@', $user->getEmail(), 2)[0]);
        $this->document->meta->add(name: 'isSocialLogin', value: $this->authenticator->isSocialLogin($user));
        $this->document->meta->add(name: 'UsernameCheck', value: $this->addFormLink(actionClass: UsernameCheck::class));

        $this->addFormAction(modelClass: ValidateUsername::class, method: 'PATCH');

        return HttpCode::Ok;
    }
}