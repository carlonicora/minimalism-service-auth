<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth as AuthService;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use Facebook\Authentication\OAuth2Client;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\Helpers\FacebookRedirectLoginHelper;

class Facebook extends AbstractAuthWebModel
{
    /**
     * @param AuthService $auth
     * @param Path $path
     * @param string|null $client_id
     * @param string|null $state
     * @param string|null $facebookToken
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        AuthService $auth,
        Path        $path,
        ?string $client_id=null,
        ?string $state=null,
        ?string $facebookToken=null,
    ): HttpCode
    {
        if ($client_id !== null){
            $auth->setClientId($client_id);
        }

        if ($state !== null){
            $auth->setState($state);
        }

        $app = new FacebookApp(
            $auth->getFacebookId(),
            $auth->getFacebookSecret()
        );
        $client = new FacebookClient();
        $oAuth = new OAuth2Client($app, $client);

        /** @noinspection ProperNullCoalescingOperatorUsageInspection */
        $accessToken = $facebookToken ?? (new FacebookRedirectLoginHelper($oAuth))->getAccessToken($path->getUrl() . 'facebook');

        $fb = new \Facebook\Facebook([
            'app_id' => $auth->getFacebookId(),
            'app_secret' => $auth->getFacebookSecret(),
            'default_graph_version' => 'v5.0',
        ]);

        $fbu = $fb->get('/me?&fields=name,email,picture', $accessToken)->getDecodedBody();

        if (!array_key_exists('email', $fbu) || empty($fbu['email'])){
            header(
                'location: '
                . $path->getUrl()
                . 'register?client_id=' . $auth->getClientId()
                . '&state=' . $auth->getState()
                . '&errorMessage=The social account does not have a valid email address'
            );
            exit;
        }

        if (($user = $auth->getAuthenticationTable()->authenticateByEmail($fbu['email'])) === null) {
            $user = $auth->getAuthenticationTable()->generateNewUser($fbu['email'], $fbu['name'], 'facebook');
            $auth->setIsNewRegistration();
            $auth->getAuthenticationTable()->activateUser($user);
        } elseif (!$user->isActive()){
            $auth->getAuthenticationTable()->activateUser($user);
        }

        $auth->setUserId($user->getId());

        header(
            'location: '
            . $path->getUrl()
            . 'auth?client_id=' . $auth->getClientId() . '&state=' . $auth->getState());

        return HttpCode::Ok;
    }
}