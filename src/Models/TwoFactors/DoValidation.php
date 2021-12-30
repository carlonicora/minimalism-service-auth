<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\TwoFactors;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use PHPGangsta_GoogleAuthenticator;

class DoValidation extends AbstractAuthWebModel
{
    /**
     * @param Auth $auth
     * @param Path $path
     * @param string $code
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        Auth $auth,
        Path $path,
        string $code,
    ): HttpCode
    {
        $authenticator = new PHPGangsta_GoogleAuthenticator();
        $tolerance = 1;

        $salt = $auth->getAuthenticationTable()->getSalt(
            userId: $auth->getUserId(),
        );

        if (!$authenticator->verifyCode($salt, $code, $tolerance)){
            return HttpCode::Unauthorized;
        }
        
        $auth->set2faValiationConfirmed();

        $this->document->meta->add(
            'redirection',
            $path->getUrl() . 'auth'
        );

        return HttpCode::Ok;
    }
}