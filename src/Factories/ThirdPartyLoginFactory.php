<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use Facebook\Facebook;
use Google_Client;

class ThirdPartyLoginFactory
{
    /**
     * ThirdPartyLoginFactory constructor.
     * @param Auth $auth
     * @param Path $path
     */
    public function __construct(
        private Auth $auth,
        private Path $path,
    )
    {
    }

    public function Facebook(Document $document): void
    {
        try {
            if ($this->auth->getFacebookId() !== null) {
                $fb = new Facebook([
                    'app_id' => $this->auth->getFacebookId(),
                    'app_secret' => $this->auth->getFacebookSecret(),
                    'default_graph_version' => 'v5.0',
                ]);
                $helper = $fb->getRedirectLoginHelper();
                $permissions = ['email'];
                $loginUrl = $helper->getLoginUrl($this->path->getUrl() . 'facebook', $permissions);

                $document->links->add(
                    new Link('facebook', $loginUrl)
                );
            }
        } catch (Exception){}
    }

    public function Google(Document $document): void
    {
        try {
            if ($this->auth->getGoogleIdentityFile() !== null) {
                $client = new Google_Client();
                $client->setAuthConfig($this->path->getRoot() . DIRECTORY_SEPARATOR . $this->auth->getGoogleIdentityFile());
                $client->setRedirectUri($this->path->getUrl() . 'google');
                $client->addScope('email');
                $client->addScope('profile');
                $authUrl = $client->createAuthUrl();

                $document->links->add(
                    new Link('google', $authUrl)
                );
            }
        } catch (Exception){}
    }

    /**
     * @param Auth $auth
     * @param Document $document
     */
    public function Apple(
        Auth $auth,
        Document $document
    ): void
    {
        try {
            if ($this->auth->getAppleClientId() !== null && $this->auth->getAppleClientSecret() !== null) {
                $auth->setState(bin2hex(random_bytes(5)));

                $authUrl = 'https://appleid.apple.com/auth/authorize' . '?' . http_build_query([
                        'response_type' => 'code',
                        'response_mode' => 'form_post',
                        'client_id' => $this->auth->getAppleClientId(),
                        'redirect_uri' => $this->path->getUrl() . 'apple',
                        'state' => $auth->getState(),
                        'scope' => 'email',
                    ]);

                $document->links->add(
                    new Link('apple', $authUrl)
                );
            }
        }
        catch (Exception){}
    }
}