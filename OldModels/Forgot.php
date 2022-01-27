<?php
namespace OldModels;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Forgot extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'forgot';

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
            new Link('doResend', $path->getUrl() . 'Accounts/Doaccountlookup')
        );

        return HttpCode::Ok;
    }
}