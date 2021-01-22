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
     * @param string|null $function
     * @throws Exception
     */
    public function __construct(
        ServiceFactory $services,
        ?string $function=null,
    )
    {
        parent::__construct($services, $function);

        $this->document->links->add(
            new Link('home', $services->getPath()->getUrl())
        );
    }
}