<?php
namespace CarloNicora\Minimalism\Services\Auth\Configurations;

use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractServiceConfigurations;
use Exception;

class AuthConfigurations  extends AbstractServiceConfigurations
{
    /** @var string|null  */
    private ?string $authInterfaceClass;

    /**
     * AuthConfigurations constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->authInterfaceClass = getenv('MINIMALISM_SERVICE_AUTH_AUTH_INTERFACE_CLASS');
    }

    /**
     * @return string|null
     */
    public function getAuthInterfaceClass(): ?string
    {
        return $this->authInterfaceClass;
    }
}