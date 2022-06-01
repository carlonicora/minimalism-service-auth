<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Redirect;
use CarloNicora\Minimalism\Services\OAuth\Data\Apps\DataObjects\App;
use CarloNicora\Minimalism\Services\OAuth\Data\Apps\IO\AppIO;
use CarloNicora\Minimalism\Services\OAuth\Data\UserScopes\DataObjects\UserScope;
use CarloNicora\Minimalism\Services\OAuth\Data\UserScopes\IO\UserScopeIO;
use Exception;

class ConfirmAuthorisation extends AbstractAuthActionModel
{
    /**
     * @param EncrypterInterface $encrypter
     * @param array $scopes
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        EncrypterInterface $encrypter,
        array $scopes,
    ): HttpCode
    {
        /** @var App $app */
        $app = $this->objectFactory->create(AppIO::class)->readByClientId($this->auth->getClientId());

        $this->objectFactory->create(UserScopeIO::class)->deleteByUserIdAppId(
            userId: $this->auth->getUserId(),
            appId: $app->getId(),
        );

        $userScopes = [];

        foreach ($scopes ?? [] as $scopeId => $scopeValue){
            $userScope = new UserScope();
            $userScope->setUserId($this->auth->getUserId());
            $userScope->setAppId($app->getId());
            $userScope->setScopeId($encrypter->decryptId($scopeId));
            $userScopes[] = $userScope;
        }

        if ($userScopes === []){
            throw ExceptionFactory::UnauthorisedScopes->create();
        }

        $this->objectFactory->create(UserScopeIO::class)->insertScopes($userScopes);

        $this->addRedirection(Redirect::class);

        return HttpCode::Created;
    }
}