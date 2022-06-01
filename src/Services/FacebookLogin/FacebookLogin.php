<?php
namespace CarloNicora\Minimalism\Services\Auth\Services\FacebookLogin;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Interfaces\SocialLoginInterface;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use Facebook\Authentication\OAuth2Client;
use Facebook\Facebook;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\Helpers\FacebookRedirectLoginHelper;

class FacebookLogin extends AbstractService implements SocialLoginInterface
{
    /**
     * @param Path $path
     * @param string|null $MINIMALISM_SERVICE_AUTH_FACEBOOK_ID
     * @param string|null $MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET
     */
    public function __construct(
        private readonly Path $path,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_FACEBOOK_ID=null,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET=null,
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
        if ($this->MINIMALISM_SERVICE_AUTH_FACEBOOK_ID === null || $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET === null) {
            return null;
        }

        $fb = new Facebook([
            'app_id' => $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_ID,
            'app_secret' => $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET,
            'default_graph_version' => 'v5.0',
        ]);
        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['email'];
        return $helper->getLoginUrl($this->path->getUrl() . 'social/facebook', $permissions);
    }

    /**
     * @param string|null $facebookToken
     * @return array
     * @throws Exception
     */
    public function validateLogin(
        ?string $facebookToken=null,
    ): array
    {
        $facebookApp = new FacebookApp(
            $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_ID,
            $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET,
        );
        $facebookClient = new FacebookClient();
        $oAuth = new OAuth2Client($facebookApp, $facebookClient);

        /** @noinspection ProperNullCoalescingOperatorUsageInspection */
        $accessToken = $facebookToken ?? (new FacebookRedirectLoginHelper($oAuth))->getAccessToken($this->path->getUrl() . 'social/facebook');

        $fb = new Facebook([
            'app_id' => $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_ID,
            'app_secret' => $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET,
            'default_graph_version' => 'v5.0',
        ]);

        $response = $fb->get('/me?&fields=name,email,picture', $accessToken)->getDecodedBody();

        if (!array_key_exists('email', $response) || empty($response['email'])){
            throw ExceptionFactory::FacebookAccountMissingEmail->create();
        }

        return $response;
    }
}