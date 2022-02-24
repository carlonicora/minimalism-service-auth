<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;

class Register extends AbstractAuthWebModel
{
    /**
     * @param string|null $client_id
     * @param string|null $state
     * @return HttpCode
     */
    public function get(
        ?string $client_id=null,
        ?string $state=null,
    ): HttpCode
    {
        header('Location: ' . $this->url . 'auth/?client_id=' . $client_id . '&state=' . $state);
        exit;
    }

}