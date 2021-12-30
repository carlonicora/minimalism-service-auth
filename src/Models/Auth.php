<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth as AuthService;
use CarloNicora\Minimalism\Services\Auth\Builders\App;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\Enums\AppReliability;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\Enums\AppStatus;
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
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        AuthService $auth,
        Path        $path,
        Builder     $builder,
        ?string     $client_id=null,
        ?string     $state=null,
    ): HttpCode
    {
        if (!empty($client_id)) {
            $auth->setClientId($client_id);
        }

        if (!empty($state)) {
            $auth->setState($state);
        }

        if (empty($auth->getClientId())) {
            throw new RuntimeException('client_id missing', 412);
        }

        if ($auth->getUserId() === null){
            return $this->redirect(
                modelClass: Login::class,
            );
        }

        $user = $auth->getAuthenticationTable()->authenticateById($auth->getUserId());

        if ($user === null){
            throw new RuntimeException('missing user details', 500);
        }

        if (!empty($user->getSalt()) && !$auth->isTwoFactorValidationConfirmed()){
            header('Location: ' . $path->getUrl() . 'TwoFactors/validation');

            exit;
        }

        $app = $auth->getAppByClientId();

        if ($app === []){
            throw new RuntimeException('App not found', 404);
        }

        $app = $app[0];

        if ($app['isActive'] === AppStatus::Inactive->value) {
            throw new RuntimeException('application is not active', 412);
        }

        if ($app['isTrusted'] === AppReliability::Trusted->value) {
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

        return HttpCode::Ok;
    }
}