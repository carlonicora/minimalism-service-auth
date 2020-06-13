<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractServiceFactory;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Configurations\AuthConfigurations;
use Exception;

class ServiceFactory extends AbstractServiceFactory {
    /**
     * serviceFactory constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services) {
        $this->configData = new AuthConfigurations();

        parent::__construct($services);
    }

    /**
     * @param ServicesFactory $services
     * @return Auth
     * @throws Exception
     */
    public function create(ServicesFactory $services) : Auth {
        return new Auth($this->configData, $services);
    }
}