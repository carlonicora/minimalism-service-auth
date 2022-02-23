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
use CarloNicora\Minimalism\Services\Auth\Data\Codes\IO\CodeIO;
use CarloNicora\Minimalism\Services\Auth\Factories\EmailFactory;
use Exception;

class Forgot extends AbstractAuthWebModel
{
    /**
     * @param bool|null $sent
     * @param EncryptedParameter|null $userId
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        ?bool $sent=false,
        ?EncryptedParameter $userId=null,
    ): HttpCode
    {
        if ($sent){
            $this->view = 'auth/resetemailsent';
        } else {
            $this->view = 'auth/forgot';
        }

        if ($userId !== null){
            $user = $this->auth->getAuthenticationTable()->authenticateById($userId->getValue());

            $userResource = new ResourceObject(type: 'user', id: $userId->getEncryptedValue());
            $userResource->attributes->add(name: 'email', value: $user->getEmail());
            $this->document->addResource(
                resource: $userResource,
            );
        }

        return HttpCode::Ok;
    }

    /**
     * @param EncrypterInterface $encrypter
     * @param string $email
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        EncrypterInterface $encrypter,
        string $email,
    ): HttpCode
    {
        $user = $this->auth->getAuthenticationTable()->authenticateByEmail($email);

        $code = $this->objectFactory->create(CodeIO::class)->generateCode($user->getId());
        $data = [
            'username' => $user->getSingleMeta(metaId: 'name') ?? $user->getUsername(),
            'code' => $code,
            'url' => $this->url . 'reset/'
                . $encrypter->encryptId($user->getId()) . '/'
                . $code . '/'
                . $this->auth->getClientId() . '/'
                . $this->auth->getState(),
        ];

        $recipient = new Recipient(
            emailAddress: $user->getEmail(),
            name: $user->getSingleMeta(metaId: 'name') ?? $user->getUsername(),
            type: RecipientType::To,
        );

        $this->objectFactory->create(EmailFactory::class)->sendEmail(
            template: 'auth/emails/forgot',
            data: $data,
            recipient: $recipient,
            title: $this->auth->getForgotEmailTitle() ?? 'Reset your account password',
        );

        $this->document->links->add(
            new Link(
                name: 'redirect',
                href: $this->url . 'forgot?sent=true&userId=' . $encrypter->encryptId($user->getId()),
            ),
        );

        return HttpCode::Ok;
    }
}