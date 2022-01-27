<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Social;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Services\FacebookLogin\FacebookLogin;
use Exception;

class Facebook extends AbstractAuthWebModel
{
    /**
     * @param FacebookLogin $facebookLogin
     * @param string|null $phlow_client_id
     * @param string|null $phlow_state
     * @param string|null $facebookToken
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        FacebookLogin $facebookLogin,
        ?string $phlow_client_id=null,
        ?string $phlow_state=null,
        ?string $facebookToken=null,
    ): HttpCode
    {
        if ($phlow_client_id !== null){
            $this->auth->setClientId($phlow_client_id);
        }

        if ($phlow_state !== null){
            $this->auth->setState($phlow_state);
        }

        $facebookUser = $facebookLogin->validateLogin($facebookToken);
        try {
            $user = $this->auth->getAuthenticationTable()->authenticateByEmail($facebookUser['email']);
        } catch (Exception) {
            $user = $this->auth->getAuthenticationTable()->generateNewUser($facebookUser['email'], $facebookUser['name'], 'facebook');
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