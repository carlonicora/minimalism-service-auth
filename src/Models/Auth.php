<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth as AuthService;
use CarloNicora\Minimalism\Services\Auth\Data\Builders\App;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\Enums\AppReliability;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\Enums\AppStatus;
use CarloNicora\Minimalism\Services\Builder\Builder;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Auth extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'auth';

    /**
     * @param AuthService $auth
     * @param Path $path
     * @param Builder $builder
     * @param string|null $client_id
     * @param string|null $state
     * @return int
     * @throws Exception
     */
    public function get(
        AuthService $auth,
        Path        $path,
        Builder     $builder,
        ?string     $client_id=null,
        ?string     $state=null,
    ): int
    {
        if (false === empty($client_id)) {
            $auth->setClientId($client_id);
        }

        if (false === empty($state)) {
            $auth->setState($state);
        }

        if (empty($auth->getClientId())) {
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

        $app = $app[0];

        if ($app['isActive'] === AppStatus::INACTIVE->value) {
            throw new RuntimeException('application is not active', 412);
        }

        if ($app['isTrusted'] === AppReliability::TRUSTED->value) {
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
            $builder->buildByData(
                resourceTransformerClass: App::class,
                data: $app
            )
        );

        return 200;
    }
}