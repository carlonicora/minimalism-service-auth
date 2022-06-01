<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ConfirmAuthorisation;
use CarloNicora\Minimalism\Services\OAuth\Data\Apps\IO\AppIO;
use CarloNicora\Minimalism\Services\OAuth\Data\AppScopes\IO\AppScopeIO;
use CarloNicora\Minimalism\Services\OAuth\Data\Scopes\IO\ScopeIO;
use CarloNicora\Minimalism\Services\OAuth\Data\UserScopes\IO\UserScopeIO;
use Exception;

class Authorisation extends AbstractAuthWebModel
{
    /**
     * @param EncrypterInterface $encrypter
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        EncrypterInterface $encrypter,
    ): HttpCode
    {
        if (!$this->auth->isAuthenticated()){
            return $this->redirect(Cancel::class);
        }

        $app = $this->objectFactory->create(AppIO::class)->readByClientId($this->auth->getClientId());

        $userScopes = $this->objectFactory->create(UserScopeIO::class)->readyByUserIdAppId(
            userId: $this->auth->getUserId(),
            appId: $app->getId(),
        );

        if ($userScopes !== [] || $app->isTrusted()){
            return $this->redirect(Redirect::class);
        }

        $this->view = Views::Authorisation->getViewFileName();

        $appScopes = $this->objectFactory->create(AppScopeIO::class)->readByAppId($app->getId());
        $appScopeResources = [];
        foreach ($appScopes as $appScope){
            $scope = $this->objectFactory->create(ScopeIO::class)->readById($appScope->getScopeId());

            $appScopeResource = new ResourceObject(type: 'appScope', id: $encrypter->encryptId($appScope->getScopeId()));
            $appScopeResource->attributes->add(name: 'name', value: $scope->getName());
            
            $appScopeResources[] = $appScopeResource;
        }
        $this->document->addResourceList($appScopeResources);

        $this->addFormAction(ConfirmAuthorisation::class);

        return HttpCode::Ok;
    }
}