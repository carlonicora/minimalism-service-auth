<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Mailer\Enums\RecipientType;
use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Recipient;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Factories\EmailFactory;
use CarloNicora\Minimalism\Services\Auth\IO\CodeIO;
use Exception;

class Forgot extends AbstractAuthWebModel
{
    /**
     * @param bool|null $sent
     * @return HttpCode
     */
    public function get(
        ?bool $sent=false,
    ): HttpCode
    {
        if ($sent){
            $this->view = 'resetemailsent';
        } else {
            $this->view = 'forgot';
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
            'username' => $user->getName() ?? $user->getUsername(),
            'code' => $code,
            'url' => $this->url . 'reset/'
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
            template: 'emails/forgot',
            data: $data,
            recipient: $recipient,
            title: $this->auth->getForgotEmailTitle() ?? 'Reset your account password',
        );

        $this->document->links->add(
            new Link(
                name: 'redirect',
                href: $this->url . 'forgot?sent=true',
            ),
        );

        return HttpCode::Ok;
    }
}