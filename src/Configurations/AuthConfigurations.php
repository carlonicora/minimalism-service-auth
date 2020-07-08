<?php
namespace CarloNicora\Minimalism\Services\Auth\Configurations;

use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractServiceConfigurations;
use CarloNicora\Minimalism\Core\Services\Exceptions\ConfigurationException;
use Exception;

class AuthConfigurations  extends AbstractServiceConfigurations
{
    /** @var string|null  */
    private ?string $authInterfaceClass;

    /** @var string  */
    private string $senderName;

    /** @var string  */
    private string $senderEmail;

    /** @var string  */
    private string $codeEmailTitle;

    /**
     * AuthConfigurations constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->authInterfaceClass = getenv('MINIMALISM_SERVICE_AUTH_AUTH_INTERFACE_CLASS');

        if (!($this->senderName = getenv('MINIMALISM_SERVICE_AUTH_SENDER_NAME'))){
            throw new ConfigurationException('MINIMALISM_SERVICE_AUTH_SENDER_NAME is a required configuration');
        }

        if (!($this->senderEmail = getenv('MINIMALISM_SERVICE_AUTH_SENDER_EMAIL'))){
            throw new ConfigurationException('MINIMALISM_SERVICE_AUTH_SENDER_EMAIL is a required configuration');
        }

        if (!($this->codeEmailTitle = getenv('MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE'))){
            throw new ConfigurationException('MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE is a required configuration');
        }
    }

    /**
     * @return string|null
     */
    public function getAuthInterfaceClass(): ?string
    {
        return $this->authInterfaceClass;
    }

    /**
     * @return string
     */
    public function getSenderName(): string
    {
        return $this->senderName;
    }

    /**
     * @return string
     */
    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    /**
     * @return string
     */
    public function getCodeEmailTitle(): string
    {
        return $this->codeEmailTitle;
    }
}