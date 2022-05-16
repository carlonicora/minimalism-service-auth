<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Services\AppleLogin\AppleLogin;
use CarloNicora\Minimalism\Services\Auth\Services\FacebookLogin\FacebookLogin;
use CarloNicora\Minimalism\Services\Auth\Services\GoogleLogin\GoogleLogin;
use Exception;

class Auth extends AbstractAuthWebModel
{
    /**
     * @param AppleLogin $appleLogin
     * @param GoogleLogin $googleLogin
     * @param FacebookLogin $facebookLogin
     * @param string $client_id
     * @param string|null $state
     * @param int|null $error
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
        ?int $error=null,
        ?string $source=null,
    ): HttpCode
    {
        $this->view = 'index';

        if ($source !== null){
            $this->auth->setSource($source);
        }
        
        $this->auth->setClientId($client_id);
        $this->generateReturnToAppLink();

        if (!empty($state)) {
            $this->auth->setState($state);
        }

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

        if ($error !== null){
            $exception = ExceptionFactory::from($error)->create();
            $this->document->addError(
                new Error(
                    httpStatusCode: $exception->getHttpCode(),
                    id: $exception->getId(),
                    errorUniqueCode: $exception->getCode(),
                    title: $exception->getMessage(),
                )
            );
        }

        return HttpCode::Ok;
    }
}