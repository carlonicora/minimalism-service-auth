<?php
namespace CarloNicora\Minimalism\Services\Auth\Configurations;

use CarloNicora\Minimalism\Core\Events\MinimalismErrorEvents;
use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractServiceConfigurations;
use Exception;

class AuthConfigurations  extends AbstractServiceConfigurations
{
    /** @var string  */
    private string $authInterfaceClass;

    /**
     * AuthConfigurations constructor.
     * @throws Exception
     */
    public function __construct()
    {
        if ((
            $this->authInterfaceClass
                =
                getenv('MINIMALISM_SERVICE_AUTH_AUTH_INTERFACE_CLASS')
            )
            === ''
        ){
            MinimalismErrorEvents::CONFIGURATION_ERROR(
                'MINIMALISM_SERVICE_AUTH_AUTH_INTERFACE_CLASS missing'
            )->throw();
        }
    }

    /**
     * @return string
     */
    public function getAuthInterfaceClass(): string
    {
        return $this->authInterfaceClass;
    }
}