<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Auth\Auth;
use Exception;
use Facebook\Facebook;
use Google_Client;

class ThirdPartyLoginFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var Auth  */
    protected Auth $auth;

    /**
     * ThirdPartyLoginFactory constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
        $this->auth = $this->services->service(Auth::class);
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
                $loginUrl = $helper->getLoginUrl($this->services->paths()->getUrl() . 'facebook', $permissions);

                $document->links->add(
                    new Link('facebook', $loginUrl)
                );
            }
        } catch (Exception $e){}
    }

    public function Google(Document $document): void
    {
        try {
            if ($this->auth->getGoogleIdentityFile() !== null) {
                $client = new Google_Client();
                $client->setAuthConfig($this->services->paths()->getRoot() . DIRECTORY_SEPARATOR . $this->auth->getGoogleIdentityFile());
                $client->setRedirectUri($this->services->paths()->getUrl() . 'google');
                $client->addScope('email');
                $client->addScope('profile');
                $authUrl = $client->createAuthUrl();

                $document->links->add(
                    new Link('google', $authUrl)
                );
            }
        } catch (Exception $e){}
    }

    public function Apple(Document $document): void
    {
        try {
            if ($this->auth->getAppleClientId() !== null && $this->auth->getAppleClientSecret() !== null) {
                $_SESSION['state'] = bin2hex(random_bytes(5));

                $authUrl = 'https://appleid.apple.com/auth/authorize' . '?' . http_build_query([
                        'response_type' => 'code',
                        'response_mode' => 'form_post',
                        'client_id' => $this->auth->getAppleClientId(),
                        'redirect_uri' => $this->services->paths()->getUrl() . 'apple',
                        'state' => $_SESSION['state'],
                        'scope' => 'name email',
                    ]);

                $document->links->add(
                    new Link('apple', $authUrl)
                );
            }
        }
        catch (Exception $e){}
    }
}