<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Social;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Objects\ModelParameters;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Authorisation;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\SocialError;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Username;
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
        try {
            $googleUser = $googleLogin->validateLogin($code);
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
            $user = $this->authenticator->authenticateByEmail($googleUser['email']);
        } catch (Exception) {
            $user = $this->authenticator->generateNewUser($googleUser['email'], $googleUser['name'] ?? '', 'google');
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