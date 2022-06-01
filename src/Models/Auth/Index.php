<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Enums\Views;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions\ValidateAccount;
use CarloNicora\Minimalism\Services\Auth\Services\AppleLogin\AppleLogin;
use CarloNicora\Minimalism\Services\Auth\Services\FacebookLogin\FacebookLogin;
use CarloNicora\Minimalism\Services\Auth\Services\GoogleLogin\GoogleLogin;
use CarloNicora\Minimalism\Services\OAuth\Data\Apps\IO\AppIO;
use Exception;

class Index extends AbstractAuthWebModel
{
    /**
     * @param AppleLogin $appleLogin
     * @param GoogleLogin $googleLogin
     * @param FacebookLogin $facebookLogin
     * @param string $client_id
     * @param string|null $state
     * @param string|null $source
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        AppleLogin $appleLogin,
        GoogleLogin $googleLogin,
        FacebookLogin $facebookLogin,
        string $client_id,
        ?string $state=null,
        ?string $source=null,
    ): HttpCode
    {
        $this->auth->cleanData();
        $this->auth->allowSaveInSession();

        $this->view = Views::Index->getViewFileName();
        
        try {
            /** @noinspection UnusedFunctionResultInspection */
            $this->objectFactory->create(AppIO::class)->readByClientId($client_id);
        } catch (Exception) {
            throw ExceptionFactory::ClientIdInvalid->create();
        }

        $this->auth->setClientId($client_id);
        $this->auth->setState($state);
        $this->auth->setSource($source);

        $this->resetAuthLink();

        if (($appleLoginLink = $appleLogin->generateLink()) !== null){
            $this->document->links->add(
                new Link(
                    name: 'apple',
                    href: $appleLoginLink,
                )
            );
        }

        if (($googleLoginLink = $googleLogin->generateLink()) !== null){
            $this->document->links->add(
                new Link(
                    name: 'google',
                    href: $googleLoginLink,
                )
            );
        }

        if (($facebookLoginLink = $facebookLogin->generateLink()) !== null){
            $this->document->links->add(
                new Link(
                    name: 'facebook',
                    href: $facebookLoginLink,
                )
            );
        }

        $this->addFormAction(modelClass: ValidateAccount::class);

        return HttpCode::Ok;
    }
}