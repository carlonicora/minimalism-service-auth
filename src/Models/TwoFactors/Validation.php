<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\TwoFactors;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Validation extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view='twofactorsvalidation';

    /**
     * @param Path $path
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        Path $path,
    ): HttpCode
    {
        $this->document->links->add(
            new Link(
                name: 'do2faValidation',
                href: $path->getUrl() . 'TwoFactors/DoValidation',
            ),
        );

        return HttpCode::Ok;
    }
}