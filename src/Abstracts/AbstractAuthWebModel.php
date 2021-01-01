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
     * @throws Exception
     */
    public function __construct(
        ServiceFactory $services
    )
    {
        parent::__construct($services);

        $this->document->links->add(
            new Link('home', $services->getPath()->getUrl())
        );
    }
}