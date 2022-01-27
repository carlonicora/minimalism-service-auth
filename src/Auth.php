<?php
namespace CarloNicora\Minimalism\Services\Auth;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Interfaces\Mailer\Enums\RecipientType;
use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Recipient;
use CarloNicora\Minimalism\Services\Auth\Interfaces\AuthenticationInterface;
use CarloNicora\Minimalism\Services\Auth\Traits\ParametersTrait;
use Exception;
use RuntimeException;

class Auth extends AbstractService
{
    use ParametersTrait;

    /** @var AuthenticationInterface|null  */
    private ?AuthenticationInterface $authInterfaceClass=null;

    /**
     * Auth constructor.
     * @param string $MINIMALISM_SERVICE_AUTH_SENDER_NAME
     * @param string $MINIMALISM_SERVICE_AUTH_SENDER_EMAIL
     * @param string|null $MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE
     * @param string|null $MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE
     */
    public function __construct(
        private string $MINIMALISM_SERVICE_AUTH_SENDER_NAME,
        private string $MINIMALISM_SERVICE_AUTH_SENDER_EMAIL,
        private ?string $MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE='',
        private ?string $MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE='',
    )
    {
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