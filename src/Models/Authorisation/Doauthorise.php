<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Authorisation;

use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\Enums\AppStatus;
use Exception;
use RuntimeException;

class Doauthorise extends AbstractAuthWebModel
{
    /**
     * @param Auth $auth
     * @return int
     * @throws Exception
     */
    public function post(
        Auth $auth,
    ): int
    {
        if (empty($auth->getClientId())) {
            throw new RuntimeException('client_id missing', 412);
        }

        $app = $auth->getAppByClientId();

        if ($app === []){
            throw new RuntimeException('App not found', 404);
        }

        $app = $app[0];

        if ($app['isActive'] === AppStatus::Inactive->value) {
            throw new RuntimeException('application is not active', 412);
        }

        $newAuth = $auth->generateAuth($app['appId']);
        $redirection = $auth->generateRedirection($app, $newAuth);

        $this->document->meta->add('redirection', $redirection);

        $auth->cleanData();

        return 200;
    }
}