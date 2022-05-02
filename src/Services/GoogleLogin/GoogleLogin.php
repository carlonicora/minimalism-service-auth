<?php
namespace CarloNicora\Minimalism\Services\Auth\Services\GoogleLogin;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Interfaces\SocialLoginInterface;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use Google_Client;

class GoogleLogin extends AbstractService implements SocialLoginInterface
{
    /**
     * @param Path $path
     * @param string|null $MINIMALISM_SERVICE_AUTH_GOOGLE_IDENTITY_FILE
     */
    public function __construct(
        private readonly Path $path,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_GOOGLE_IDENTITY_FILE=null,
    )
    {
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function generateLink(
    ): ?string
    {
        if ($this->MINIMALISM_SERVICE_AUTH_GOOGLE_IDENTITY_FILE === null) {
            return null;
        }

        $client = new Google_Client();
        $client->setAuthConfig($this->path->getRoot() . DIRECTORY_SEPARATOR . $this->MINIMALISM_SERVICE_AUTH_GOOGLE_IDENTITY_FILE);
        $client->setRedirectUri($this->path->getUrl() . 'social/google');
        $client->addScope('email');
        $client->addScope('profile');
        return $client->createAuthUrl();
    }

    /**
     * @param string|null $code
     * @return array
     * @throws Exception
     */
    public function validateLogin(
        ?string $code=null,
    ): array
    {
        $client = new Google_Client();
        $client->setAuthConfig($this->path->getRoot() . DIRECTORY_SEPARATOR . $this->MINIMALISM_SERVICE_AUTH_GOOGLE_IDENTITY_FILE);
        $client->setRedirectUri($this->path->getUrl() . 'social/google');
        $client->addScope('email');
        $client->addScope('profile');

        /** @noinspection UnusedFunctionResultInspection */
        $client->fetchAccessTokenWithAuthCode($code);
        $response = $client->verifyIdToken();

        if (!array_key_exists('email', $response)){
            header('Location:' . $this->path->getUrl() . 'index?error=' . ExceptionFactory::GoogleAccountMissingEmail->value);
            exit;
        }

        return $response;
    }
}