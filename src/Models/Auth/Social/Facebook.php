<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Social;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Objects\ModelParameters;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Authorisation;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\SocialError;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Username;
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

        try {
            $facebookUser = $facebookLogin->validateLogin($facebookToken);
        } catch (Exception $e) {
            $parameters = new ModelParameters();
            $parameters->addNamedParameter(name: 'error', value: $e->getMessage());

            return $this->redirect(
                modelClass: SocialError::class,
                function: 'get',
                parameters: $parameters,
            );
        }

        try {
            $user = $this->authenticator->authenticateByEmail($facebookUser['email']);
        } catch (Exception) {
            $user = $this->authenticator->generateNewUser($facebookUser['email'], $facebookUser['name'], 'facebook');
            $this->auth->setIsNewRegistration();
        }

        if (!$user->isActive()){
            $this->authenticator->activateUser($user);
            $user->setIsActive(true);
        }

        $this->auth->setUserId($user->getId());
        $this->auth->setIsAuthenticated(true);

        if ($this->auth->isNewRegistration()){
            header('Location:' . $this->getRedirectionLink(Username::class));
        } else {
            header('Location:' . $this->getRedirectionLink(Authorisation::class));
        }

        exit;
    }
}