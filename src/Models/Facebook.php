<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Services\FacebookLogin\FacebookLogin;
use Exception;

class Facebook extends AbstractAuthWebModel
{
    /**
     * @param FacebookLogin $facebookLogin
     * @param string|null $phlow_client_id
     * @param string|null $phlow_state
     * @param string|null $facebookToken
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        FacebookLogin $facebookLogin,
        ?string $phlow_client_id=null,
        ?string $phlow_state=null,
        ?string $facebookToken=null,
    ): HttpCode
    {
        return $this->redirect(
            modelClass: Social\Facebook::class,
        );
    }
}