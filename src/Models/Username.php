<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Mailer\Enums\RecipientType;
use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Recipient;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\User;
use CarloNicora\Minimalism\Services\Auth\Factories\EmailFactory;
use CarloNicora\Minimalism\Services\Auth\IO\CodeIO;
use Exception;

class Username extends AbstractAuthWebModel
{
    /**
     * @param EncrypterInterface $encrypter
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        EncrypterInterface $encrypter,
    ): HttpCode
    {
        $this->view = 'username';

        $user = $this->auth->getAuthenticationTable()->authenticateById($this->auth->getUserId());

        $userResource = new ResourceObject(
            type: 'user',
            id: $encrypter->encryptId($user->getId()),
        );
        $userResource->attributes->add(
            name: 'username',
            value: $user->getUsername(),
        );

        $this->document->addResource(
            resource: $userResource,
        );

        return HttpCode::Ok;
    }

    /**
     * @param EncrypterInterface $encrypter
     * @param string $username
     * @param string|null $password
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        EncrypterInterface $encrypter,
        string $username,
        ?string $password=null,
    ): HttpCode
    {
        $user = $this->auth->getAuthenticationTable()->authenticateById($this->auth->getUserId());

        if ($username !== $user->getUsername()){
            $this->auth->getAuthenticationTable()->updateUsername($user->getId(), $username);
        }

        if ($password !== null){
            $this->auth->getAuthenticationTable()->updatePassword($user->getId(), password_hash($password, PASSWORD_BCRYPT));
        }

        $this->sendCode($encrypter, $user);

        return HttpCode::NoContent;
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
            'url' => $this->url . 'code'
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
            template: 'code',
            data: $data,
            recipient: $recipient,
            title: $this->auth->getForgotEmailTitle() ?? 'Your passwordless access code and link',
        );
    }
}