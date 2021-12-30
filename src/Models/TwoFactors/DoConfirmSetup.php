<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\TwoFactors;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use PHPGangsta_GoogleAuthenticator;

class DoConfirmSetup extends AbstractAuthWebModel
{
    /**
     * @param Auth $auth
     * @param Path $path
     * @param string|null $code
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        Auth $auth,
        Path $path,
        ?string $code=null,
    ): HttpCode
    {
        $authenticator = new PHPGangsta_GoogleAuthenticator();
        $tolerance = 1;

        $salt = $auth->getSalt();

        if (!$authenticator->verifyCode($salt, $code, $tolerance)){
            return HttpCode::Unauthorized;
        }

        $auth->set2faValiationConfirmed();

        $userId = $auth->getUserId();
        $auth->getAuthenticationTable()->updateSalt(
            userId: $userId,
            salt: $salt,
        );

        $this->document->meta->add(
            'redirection',
            $path->getUrl() . 'twofactors/backupcodes'
        );

        return HttpCode::Ok;
    }
}