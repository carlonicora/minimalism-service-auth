<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Code;
use Exception;

class ResendCode extends AbstractAuthActionModel
{
    /**
     * @return HttpCode
     * @throws Exception
     */
    public function post(
    ): HttpCode
    {
        $user = $this->authenticator->authenticateById($this->auth->getUserId());
        $this->auth->sendCodeEmail($user);

        $this->addRedirection(Code::class);

        return HttpCode::Accepted;
    }
}