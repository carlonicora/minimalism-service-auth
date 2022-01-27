<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Mailer\Enums\RecipientType;
use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Recipient;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\User;
use CarloNicora\Minimalism\Services\Auth\Factories\EmailFactory;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\IO\CodeIO;
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
            $this->document->addResource(
                new ResourceObject(
                    type: 'user',
                    id: $userId->getEncryptedValue(),
                )
            );
        } else {
            try {
                $user = $this->auth->getAuthenticationTable()->authenticateByEmail($email);

                if (!$user->isActive()){
                    $this->auth->setIsNewRegistration();

                    $this->view = 'username';
                } else if ($user->getPassword() !== null) {
                    $this->view = 'password';
                } else {
                    $this->view = 'code';

                    $this->sendCode($encrypter, $user);
                }
            } catch (Exception) {
                $user = $this->auth->getAuthenticationTable()->generateNewUser($email);
                $this->auth->setIsNewRegistration();

                $this->view = 'username';
            }

            $this->auth->setUserId($user->getId());

            $userResource = new ResourceObject(
                type: 'user',
                id: $encrypter->encryptId($user->getId()),
            );
            $userResource->attributes->add(name: 'username', value: $user->getUsername());

            $this->document->addResource(
                resource: $userResource,
            );
        }

        return HttpCode::Ok;
    }

    /**
     * @param EncrypterInterface $encrypter
     * @param EncryptedParameter|null $userId
     * @param bool|null $resendCode
     * @param string|null $password
     * @param int|null $code
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        EncrypterInterface $encrypter,
        ?EncryptedParameter $userId=null,
        ?bool $resendCode=null,
        ?string $password=null,
        ?int $code=null,
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
            $this->sendCode($encrypter, $user);

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
            $this->objectFactory->create(CodeIO::class)->validate($user->getId(), $code);
        } else {
            throw ExceptionFactory::PasswordOrCodeMising->create();
        }

        if(!$user->isActive()) {
            $this->auth->getAuthenticationTable()->activateUser($user);
        }
        $this->auth->setUserId($user->getId());

        $this->addCorrectRedirection();

        return HttpCode::Ok;
    }

    /**
     * @param EncrypterInterface $encrypter
     * @param User $user
     * @return void
     * @throws Exception
     */
    private function sendCode(
        EncrypterInterface $encrypter,
        User $user,
    ): void
    {
        $code = $this->objectFactory->create(CodeIO::class)->generateCode($user->getId());
        $data = [
            'username' => $user->getName() ?? $user->getUsername(),
            'code' => $code,
            'url' => $this->url . 'code/'
                . $encrypter->encryptId($user->getId()) . '/'
                . $code . '/'
                . $this->auth->getClientId() . '/'
                . $this->auth->getState(),
        ];

        $recipient = new Recipient(
            emailAddress: $user->getEmail(),
            name: $user->getName() ?? $user->getUsername(),
            type: RecipientType::To,
        );

        $this->objectFactory->create(EmailFactory::class)->sendEmail(
            template: 'emails/logincode',
            data: $data,
            recipient: $recipient,
            title: $this->auth->getCodeEmailTitle() ?? 'Your passwordless access code and link',
        );
    }
}