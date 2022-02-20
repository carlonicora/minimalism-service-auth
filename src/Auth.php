<?php
namespace CarloNicora\Minimalism\Services\Auth;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Mailer\Enums\RecipientType;
use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Recipient;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\IO\CodeIO;
use CarloNicora\Minimalism\Services\Auth\Factories\EmailFactory;
use CarloNicora\Minimalism\Services\Auth\Traits\ParametersTrait;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\Users\Data\Users\DataObjects\User;
use CarloNicora\Minimalism\Services\Users\Interfaces\AuthenticationInterface;
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
     * @param string|null $MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE
     */
    public function __construct(
        private Path $path,
        private EncrypterInterface $encrypter,
        private string $MINIMALISM_SERVICE_AUTH_SENDER_NAME,
        private string $MINIMALISM_SERVICE_AUTH_SENDER_EMAIL,
        private ?string $MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE='',
        private ?string $MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE='',
    )
    {
    }

    /**
     * @param User $user
     * @return void
     * @throws Exception
     */
    public function sendCode(
        User $user,
    ): void
    {
        $code = $this->objectFactory->create(CodeIO::class)->generateCode($user->getId());
        $data = [
            'username' => $user->getSingleMeta(metaId: 'name') ?? $user->getUsername(),
            'code' => $code,
            'url' => $this->path->getUrl() . 'code/'
                . $this->encrypter->encryptId($user->getId()) . '/'
                . $code . '/'
                . $this->getClientId() . '/'
                . $this->getState(),
        ];

        $recipient = new Recipient(
            emailAddress: $user->getEmail(),
            name: $user->getSingleMeta(metaId: 'name') ?? $user->getUsername(),
            type: RecipientType::To,
        );

        $this->objectFactory->create(EmailFactory::class)->sendEmail(
            template: 'emails/logincode',
            data: $data,
            recipient: $recipient,
            title: $this->getCodeEmailTitle() ?? 'Your passwordless access code and link',
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
     * @return string
     */
    public function getCodeEmailTitle(
    ): string
    {
        return $this->MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE;
    }

    /**
     * @return string
     */
    public function getForgotEmailTitle(
    ): string
    {
        return $this->MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE;
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