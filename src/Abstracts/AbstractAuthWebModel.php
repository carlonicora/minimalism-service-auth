<?php
namespace CarloNicora\Minimalism\Services\Auth\Abstracts;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Factories\ServiceFactory;
use Exception;

class AbstractAuthWebModel extends AbstractModel
{
    /**
     * AbstractAuthWebModel constructor.
     * @param ServiceFactory $services
     * @param array $modelDefinition
     * @param string|null $function
     * @throws Exception
     */
    public function __construct(
        private ServiceFactory $services,
        private array $modelDefinition,
        private ?string $function=null,
    )
    {
        parent::__construct(
            $this->services,
            $this->modelDefinition,
            $this->function
        );

        $this->document->links->add(
            new Link('home', $services->getPath()->getUrl())
        );
    }
}