<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Social;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\DataObjects\AppleId;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\IO\AppleIdIO;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
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
        $appleUser = $appleLogin->validateLogin(
            code: $code,
            state: $state,
        );

        if (array_key_exists('email', $appleUser)) {
            try {
                $user = $this->auth->getAuthenticationTable()->authenticateByEmail($appleUser['email']);
            } catch (Exception) {
                try {
                    $appleId = $this->objectFactory->create(AppleIdIO::class)->readByAppleId($appleUser['sub']);
                    $user = $this->auth->getAuthenticationTable()->authenticateById($appleId->getUserId());
                } catch (Exception) {
                    $user = $this->auth->getAuthenticationTable()->generateNewUser($appleUser['email'], $appleUser['name'] ?? '', 'apple');
                    $this->auth->setIsNewRegistration();
                    if(!$user->isActive()) {
                        $this->auth->getAuthenticationTable()->activateUser($user);
                    }

                    $appleId = new AppleId();
                    $appleId->setAppleId($appleUser['sub']);
                    $appleId->setUserId($user->getId());
                    /** @noinspection UnusedFunctionResultInspection */
                    $this->objectFactory->create(AppleIdIO::class)->insert($appleId);
                }
            }
        } else {
            try {
                $appleId = $this->objectFactory->create(AppleIdIO::class)->readByAppleId($appleUser['sub']);
                try {
                    $user = $this->auth->getAuthenticationTable()->authenticateById($appleId->getUserId());
                } catch (Exception) {
                    header('Location:' . $this->url . 'index?error=' . ExceptionFactory::AppleIdNotMatchingAccount->value);
                    exit;
                }
            } catch (Exception) {
                header('Location:' . $this->url . 'index?error=' . ExceptionFactory::AppleIdNotFound->value);
                exit;
            }
        }

        if (!$user->isActive()){
            $this->auth->getAuthenticationTable()->activateUser($user);
        }

        $this->auth->setUserId($user->getId());

        if ($this->auth->isNewRegistration()){
            $this->auth->sendCode($user);

            header('Location:' . $this->url . 'username');
            exit;
        }

        $this->addCorrectRedirection(true);

        return HttpCode::Ok;
    }
}