<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Forgot extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'forgot';

    /**
     * @param Path $path
     * @return int
     * @throws Exception
     */
    public function get(
        Path $path,
    ): int
    {
        $this->document->links->add(
            new Link('doResend', $path->getUrl() . 'Accounts/Doaccountlookup')
        );

        return 200;
    }
}