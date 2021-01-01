<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Authorisation;

use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use Exception;
use RuntimeException;

class Doauthorise extends AbstractAuthWebModel
{
    /**
     * @param Auth $auth
     * @return int
     * @throws DbRecordNotFoundException|Exception
     */
    public function get(
        Auth $auth,
    ): int
    {
        if ($auth->getClientId() === null) {
            throw new RuntimeException('client_id missing', 412);
        }

        $app = $auth->getAppByClientId();

        if (!$app['isActive']) {
            throw new RuntimeException('application is not active', 412);
        }

        $newAuth = $auth->generateAuth($app['appId']);
        $redirection = $auth->generateRedirection($app, $newAuth);

        $this->document->meta->add('redirection', $redirection);

        $auth->cleanData();

        return 200;
    }
}