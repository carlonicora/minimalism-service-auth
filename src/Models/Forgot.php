<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

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
     * @return HttpCode
     */
    public function get(
    ): HttpCode
    {
        $this->view = 'forgot';

        return HttpCode::Ok;
    }

    /**
     * @param EncrypterInterface $encrypter
     * @param string $email
     * @return HttpCode
     */
    public function post(
        EncrypterInterface $encrypter,
        string $email,
    ): HttpCode
    {
        $this->view = 'resetemailsent';

        try {
            $user = $this->auth->getAuthenticationTable()->authenticateByEmail($email);

            $code = $this->objectFactory->create(CodeIO::class)->generateCode($user->getId());
            $data = [
                'username' => $user->getName() ?? $user->getUsername(),
                'code' => $code,
                'url' => $this->url . 'reset'
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
                template: 'forgot',
                data: $data,
                recipient: $recipient,
                title: $this->auth->getForgotEmailTitle() ?? 'Reset your account password',
            );
        } catch (Exception) {
        }

        return HttpCode::NoContent;
    }
}