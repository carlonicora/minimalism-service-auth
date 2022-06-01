<?php
namespace CarloNicora\Minimalism\Services\Auth;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Mailer\Enums\RecipientType;
use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Recipient;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\IO\CodeIO;
use CarloNicora\Minimalism\Services\Auth\Data\Users\DataObjects\User;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Factories\EmailFactory;
use CarloNicora\Minimalism\Services\Auth\Interfaces\AuthenticationInterface;
use CarloNicora\Minimalism\Services\Auth\Traits\ParametersTrait;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Auth extends AbstractService
{
    use ParametersTrait;

    /** @var AuthenticationInterface|null  */
    private ?AuthenticationInterface $authInterfaceClass=null;

    /**
     * Auth constructor.
     * @param Path $path
     * @param EncrypterInterface $encrypter
     * @param string $MINIMALISM_SERVICE_AUTH_SENDER_NAME
     * @param string $MINIMALISM_SERVICE_AUTH_SENDER_EMAIL
     * @param string|null $MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE
     * @param string|null $MINIMALISM_SERVICE_AUTH_REGISTRATION_EMAIL_TITLE
     * @param string|null $MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE
     */
    public function __construct(
        private readonly Path $path,
        private readonly EncrypterInterface $encrypter,
        private readonly string $MINIMALISM_SERVICE_AUTH_SENDER_NAME,
        private readonly string $MINIMALISM_SERVICE_AUTH_SENDER_EMAIL,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE=null,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_REGISTRATION_EMAIL_TITLE=null,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE=null,
    )
    {
    }

    /**
     * @param User $user
     * @return void
     * @throws Exception
     */
    public function sendForgotEmail(
        User $user,
    ): void
    {
        $code = $this->objectFactory->create(CodeIO::class)->generateCode($user->getId());

        $url = $this->path->getUrl() . 'auth/reset/'
            . $this->encrypter->encryptId($user->getId()) . '/'
            . $code . '/'
            . $this->getClientId() . '/'
            . $this->getState();

        if ($this->getSource() !== null){
            $url .= '&source=' . $this->getSource();
        }
        
        $data = [
            'username' => $user->getName() ?? $user->getUsername(),
            'code' => $code,
            'url' => $url,
        ];

        $recipient = new Recipient(
            emailAddress: $user->getEmail(),
            name: $user->getName() ?? $user->getUsername(),
            type: RecipientType::To,
        );

        $this->objectFactory->create(EmailFactory::class)->sendEmail(
            template: Views::EmailForgotPassword->getViewFileName(),
            data: $data,
            recipient: $recipient,
            title: $this->MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE ?? 'Reset your account password',
        );
    }

    /**
     * @param string $email
     * @return void
     * @throws Exception
     */
    public function sendActivationEmail(
        string $email,
    ): void
    {
        $code = $this->objectFactory->create(CodeIO::class)->generateCode(email: $email);

        $url = $this->path->getUrl() . 'auth/emailactivation/'
            . $email . '/'
            . $code . '/'
            . $this->getClientId() . '/'
            . $this->getState();

        if ($this->getSource() !== null){
            $url .= '&source=' . $this->getSource();
        }

        $data = [
            'code' => $code,
            'url' => $url,
        ];

        $recipient = new Recipient(
            emailAddress: $email,
            name: $email,
            type: RecipientType::To,
        );

        $this->objectFactory->create(EmailFactory::class)->sendEmail(
            template: Views::EmailActivationCode->getViewFileName(),
            data: $data,
            recipient: $recipient,
            title: $this->MINIMALISM_SERVICE_AUTH_REGISTRATION_EMAIL_TITLE ?? 'Activate your account',
        );
    }

    /**
     * @param User $user
     * @return void
     * @throws Exception
     */
    public function sendCodeEmail(
        User $user,
    ): void
    {
        $code = $this->objectFactory->create(CodeIO::class)->generateCode($user->getId());
        $data = [
            'username' => $user->getName() ?? $user->getUsername(),
            'code' => $code,
            'url' => $this->path->getUrl() . 'auth/code/'
                . $this->encrypter->encryptId($user->getId()) . '/'
                . $code . '/'
                . $this->getClientId() . '/'
                . $this->getState(),
        ];

        $recipient = new Recipient(
            emailAddress: $user->getEmail(),
            name: $user->getName() ?? $user->getUsername(),
            type: RecipientType::To,
        );

        $this->objectFactory->create(EmailFactory::class)->sendEmail(
            template: Views::EmailLoginCode->getViewFileName(),
            data: $data,
            recipient: $recipient,
            title: $this->MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE ?? 'Your passwordless access code and link',
        );
    }

    /**
     * @return Recipient
     */
    public function getSender(
    ): Recipient
    {
        return new Recipient(
            emailAddress: $this->MINIMALISM_SERVICE_AUTH_SENDER_EMAIL,
            name: $this->MINIMALISM_SERVICE_AUTH_SENDER_NAME,
            type: RecipientType::Sender,
        );
    }

    /**
     * @param AuthenticationInterface $authInterfaceClass
     */
    public function setAuthInterfaceClass(
        AuthenticationInterface $authInterfaceClass,
    ): void
    {
        $this->authInterfaceClass = $authInterfaceClass;
    }

    /**
     * @return AuthenticationInterface
     * @throws Exception
     */
    public function getAuthenticationTable(
    ): AuthenticationInterface
    {
        if ($this->authInterfaceClass === null){
            throw new RuntimeException('The authorization interface is not configured', 500);
        }

        return $this->authInterfaceClass;
    }

    /**
     *
     */
    public function cleanData(): void
    {
        $this->cleanParametersData();
    }

    /**
     * @throws Exception
     */
    public function initialise(): void {
        if (isset($_SESSION)) {
            $this->readParametersFromSession();
        }
    }

    /**
     *
     */
    public function destroy(): void
    {
        if (isset($_SESSION)) {
            $this->saveParametersInSession();
        }
    }
}