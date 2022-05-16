<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\OAuth\OAuth;
use Exception;

class Cancel extends AbstractAuthWebModel
{
    /**
     * @param OAuth $OAuth
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        OAuth $OAuth,
    ): HttpCode
    {
        $redirection = $OAuth->generateRedirection(
            clientId: $this->auth->getClientId(),
            state: $this->auth->getState()
        );

        $this->auth->cleanData();

        header('Location:' . $redirection);
        exit;
    }
}