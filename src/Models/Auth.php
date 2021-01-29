<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\Builders\App;
use CarloNicora\Minimalism\Services\JsonApi\JsonApi;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Auth extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'auth';

    /**
     * @param \CarloNicora\Minimalism\Services\Auth\Auth $auth
     * @param Path $path
     * @param JsonApi $jsonApi
     * @param string|null $client_id
     * @param string|null $state
     * @return int
     * @throws Exception
     */
    public function get(
        \CarloNicora\Minimalism\Services\Auth\Auth $auth,
        Path $path,
        JsonApi $jsonApi,
        ?string $client_id=null,
        ?string $state=null,
    ): int
    {
        if ($client_id !== null) {
            $auth->setClientId($client_id);
        }

        if ($state !== null) {
            $auth->setState($state);
        }

        if ($auth->getClientId() === null) {
            throw new RuntimeException('client_id missing', 412);
        }

        if ($auth->getUserId() === null){
            $this->redirection = Login::class;
            $this->redirectionParameters = null;
            return 302;
        }

        $app = $auth->getAppByClientId();

        if ($app === []){
            throw new RuntimeException('App not found', 404);
        }

        if (!$app['isActive']) {
            throw new RuntimeException('application is not active', 412);
        }

        if ($app['isTrusted']) {
            $authorisation = $auth->generateAuth($app['appId']);
            $redirection = $auth->generateRedirection($app, $authorisation);

            $auth->cleanData();

            header('Location: ' . $redirection);
            exit;
        }

        $this->document->links->add(
            new Link('authorise', $path->getUrl() . 'Authorisation/Doauthorise')
        );

        $this->document->addResourceList(
            $jsonApi->generateResourceObjectByFieldValue(
                App::class,
                null,
                App::attributeId(),
                $app['appId'],
                true
            )
        );

        return 200;
    }
}