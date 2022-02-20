<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\IO\CodeIO;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use Exception;

class Login extends AbstractAuthWebModel
{
    /**
     * @param EncrypterInterface $encrypter
     * @param string|null $email
     * @param bool|null $forceCode
     * @param EncryptedParameter|null $userId
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        EncrypterInterface $encrypter,
        ?string $email=null,
        ?bool $forceCode=null,
        ?EncryptedParameter $userId=null,
    ): HttpCode{
        if ($forceCode && $userId !== null){
            $this->view = 'code';

            $user = $this->auth->getAuthenticationTable()->authenticateById($userId->getValue());
            
            $userResource = new ResourceObject(
                type: 'user',
                id: $userId->getEncryptedValue(),
            );
        } else {
            try {
                $user = $this->auth->getAuthenticationTable()->authenticateByEmail($email);

                if ($user->getPassword() !== null) {
                    $this->view = 'password';
                } else {
                    $this->view = 'code';
                    $this->auth->sendCode($user);

                    if (!$user->isActive()){
                        $this->auth->setIsNewRegistration();
                        $this->document->meta->add(name: 'activation', value: true);
                    }
                }
            } catch (Exception) {
                $user = $this->auth->getAuthenticationTable()->generateNewUser($email);
                $this->auth->setIsNewRegistration();
                $this->view = 'code';
                $this->auth->sendCode($user);
                $this->document->meta->add(name: 'activation', value: true);
            }

            $userResource = new ResourceObject(
                type: 'user',
                id: $encrypter->encryptId($user->getId()),
            );
        }

        $this->auth->setUserId($user->getId());

        $userResource->attributes->add(name: 'email', value: $user->getEmail());

        $this->document->addResource(
            resource: $userResource,
        );

        return HttpCode::Ok;
    }

    /**
     * @param EncrypterInterface $encrypter
     * @param EncryptedParameter|null $userId
     * @param bool|null $resendCode
     * @param string|null $password
     * @param string|null $code
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        EncrypterInterface $encrypter,
        ?EncryptedParameter $userId=null,
        ?bool $resendCode=null,
        ?string $password=null,
        ?string $code=null,
    ): HttpCode
    {
        if ($userId === null && $this->auth->getUserId() === null){
            throw ExceptionFactory::MissingUserInformation->create();
        }

        if ($userId !== null) {
            $user = $this->auth->getAuthenticationTable()->authenticateById($userId->getValue());
        } else {
            $user = $this->auth->getAuthenticationTable()->authenticateById($this->auth->getUserId());
        }

        $this->auth->setUserId($user->getId());

        if ($resendCode !== null){
            $this->auth->sendCode($user);

            $this->document->links->add(
                new Link(
                    name: 'redirect',
                    href: $this->url . 'login?forceCode=true&userId=' . $encrypter->encryptId($user->getId()),
                ),
            );

            return HttpCode::Ok;
        }

        if ($password !== null){
            if (!password_verify($password, $user->getPassword())){
                throw ExceptionFactory::WrongPassword->create();
            }
        } elseif ($code !== null) {
            $this->objectFactory->create(CodeIO::class)->validate($user->getId(), (int)$code);
        } else {
            throw ExceptionFactory::PasswordOrCodeMising->create();
        }

        if(!$user->isActive()) {
            $this->auth->getAuthenticationTable()->activateUser($user);
        }
        $this->auth->setUserId($user->getId());
        $this->auth->setIsAuthenticated(true);

        if ($this->auth->isNewRegistration()){
            $this->document->links->add(
                new Link(
                    name: 'redirect',
                    href: $this->url . 'username',
                ),
            );
        } else {
            $this->addCorrectRedirection();
        }

        return HttpCode::Ok;
    }
}