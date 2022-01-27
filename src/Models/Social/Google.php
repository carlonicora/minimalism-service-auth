<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Social;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Services\GoogleLogin\GoogleLogin;
use Exception;

class Google extends AbstractAuthWebModel
{
    /**
     * @param GoogleLogin $googleLogin
     * @param string|null $code
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        GoogleLogin $googleLogin,
        ?string $code=null,
    ): HttpCode
    {
        $googleUser = $googleLogin->validateLogin($code);

        try {
            $user = $this->auth->getAuthenticationTable()->authenticateByEmail($googleUser['email']);
        } catch (Exception) {
            $user = $this->auth->getAuthenticationTable()->generateNewUser($googleUser['email'], $googleUser['name'] ?? '', 'google');
            $this->auth->setIsNewRegistration();
        }

        if (!$user->isActive()){
            $this->auth->getAuthenticationTable()->activateUser($user);
        }

        $this->auth->setUserId($user->getId());

        $this->addCorrectRedirection(true);

        return HttpCode::Ok;
    }
}