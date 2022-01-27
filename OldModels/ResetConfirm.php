<?php
namespace OldModels;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;

class ResetConfirm extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'resetconfirm';

    /**
     * @return HttpCode
     */
    public function get(
    ): HttpCode
    {
        return HttpCode::Ok;
    }
}