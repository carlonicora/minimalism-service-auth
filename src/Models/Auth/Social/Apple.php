<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Social;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Objects\ModelParameters;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\DataObjects\AppleId;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\IO\AppleIdIO;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Authorisation;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\SocialError;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Username;
use CarloNicora\Minimalism\Services\Auth\Services\AppleLogin\AppleLogin;
use Exception;

class Apple extends AbstractAuthWebModel
{
    /**
     * @param AppleLogin $appleLogin
     * @param string|null $code
     * @param string|null $state
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        AppleLogin $appleLogin,
        ?string $code=null,
        ?string $state=null,
    ): HttpCode
    {
        try {
            $appleUser = $appleLogin->validateLogin(
                code: $code,
                state: $state,
            );
        } catch (Exception $e) {
            $parameters = new ModelParameters();
            $parameters->addNamedParameter(name: 'error', value: $e->getMessage());

            return $this->redirect(
                modelClass: SocialError::class,
                function: 'get',
                parameters: $parameters,
            );
        }

        if (array_key_exists('email', $appleUser)) {
            try {
                $user = $this->authenticator->authenticateByEmail($appleUser['email']);
            } catch (Exception) {
                try {
                    $appleIdData = $this->objectFactory->create(AppleIdIO::class)->readByAppleId($appleUser['sub']);
                    $user = $this->authenticator->authenticateById($appleIdData['userId']);
                } catch (Exception) {
                    $user = $this->authenticator->generateNewUser($appleUser['email'], $appleUser['name'] ?? '', 'apple');
                    $this->auth->setIsNewRegistration();


                    /** @noinspection UnusedFunctionResultInspection */
                    $appleId = new AppleId();
                    $appleId->setAppleId($appleUser['sub']);
                    $appleId->setUserId($user->getId());

                    /** @noinspection UnusedFunctionResultInspection */
                    $this->objectFactory->create(AppleIdIO::class)->insert($appleId);
                }
            }
        } else {
            try {
                $appleIdData = $this->objectFactory->create(AppleIdIO::class)->readByAppleId($appleUser['sub']);
                try {
                    $user = $this->authenticator->authenticateById($appleIdData['userId']);
                } catch (Exception) {
                    $parameters = new ModelParameters();
                    $parameters->addNamedParameter(name: 'error', value: ExceptionFactory::AppleIdNotMatchingAccount->create()->getMessage());

                    return $this->redirect(
                        modelClass: SocialError::class,
                        function: 'get',
                        parameters: $parameters,
                    );
                }
            } catch (Exception) {
                $parameters = new ModelParameters();
                $parameters->addNamedParameter(name: 'error', value: ExceptionFactory::AppleIdNotFound->create()->getMessage());

                return $this->redirect(
                    modelClass: SocialError::class,
                    function: 'get',
                    parameters: $parameters,
                );
            }
        }

        if(!$user->isActive()) {
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