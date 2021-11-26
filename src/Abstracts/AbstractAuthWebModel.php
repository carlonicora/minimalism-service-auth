<?php
namespace CarloNicora\Minimalism\Services\Auth\Abstracts;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Factories\MinimalismFactories;
use Exception;

class AbstractAuthWebModel extends AbstractModel
{
    /**
     * AbstractAuthWebModel constructor.
     * @param MinimalismFactories $minimalismFactories
     * @param string|null $function
     * @throws Exception
     */
    public function __construct(
        MinimalismFactories $minimalismFactories,
        ?string $function=null,
    )
    {
        parent::__construct(
            $minimalismFactories,
            $function
        );

        $this->document->links->add(
            new Link('home', $minimalismFactories->getServiceFactory()->getPath()->getUrl())
        );
    }
}