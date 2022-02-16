<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\OAuth\OAuth;
use Exception;

class Redirect extends AbstractAuthWebModel
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
            userId: $this->auth->isAuthenticated() ? $this->auth->getUserId() : null,
            state: $this->auth->getState()
        );

        if ($this->auth->isNewRegistration()){
            $redirection .= '&newRegistration=true';
        }

        $this->auth->cleanData();

        header('Location:' . $redirection);
        exit;
    }
}