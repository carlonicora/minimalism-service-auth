<?php
namespace CarloNicora\Minimalism\Services\Auth\Abstracts;

use CarloNicora\JsonApi\Objects\Link;
use Exception;

class AbstractAuthActionModel extends AbstractAuthModel
{
    /**
     * @param string $pageClass
     * @param array|null $positionedParameters
     * @param array $parameters
     * @return void
     * @throws Exception
     */
    protected function addRedirection(
        string $pageClass,
        ?array $positionedParameters=null,
        array  $parameters = [],
    ): void
    {
        $this->document->links->add(
            new Link(
                name: 'redirect',
                href: $this->getRedirectionLink(
                    pageClass: $pageClass,
                    positionedParameters: $positionedParameters,
                    parameters: $parameters,
                ),
            )
        );
    }
}