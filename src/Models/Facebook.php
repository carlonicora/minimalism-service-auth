<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use Facebook\Authentication\OAuth2Client;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\Helpers\FacebookRedirectLoginHelper;

class Facebook extends AbstractAuthWebModel
{
    /**
     * @param \CarloNicora\Minimalism\Services\Auth\Auth $auth
     * @param Path $path
     * @return int
     * @throws Exception
     */
    public function get(
        \CarloNicora\Minimalism\Services\Auth\Auth $auth,
        Path $path,
    ): int
    {
        $app = new FacebookApp(
            $auth->getFacebookId(),
            $auth->getFacebookSecret()
        );
        $client = new FacebookClient();
        $oAuth = new OAuth2Client($app, $client);

        $helper = new FacebookRedirectLoginHelper($oAuth);

        $accessToken = $helper->getAccessToken($path->getUrl() . 'facebook');

        $fb = new \Facebook\Facebook([
            'app_id' => $auth->getFacebookId(),
            'app_secret' => $auth->getFacebookSecret(),
            'default_graph_version' => 'v5.0',
        ]);

        $facebookUser = $fb->get('/me?&fields=name,email,picture', $accessToken);
        $fbu = $facebookUser->getDecodedBody();

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
        } elseif ($user['isActive'] === false){
            $auth->getAuthenticationTable()->activateUser($user);
        }

        $auth->setUserId($user['userId']);

        header(
            'location: '
            . $path->getUrl()
            . 'auth?client_id=' . $auth->getClientId() . '&state=' . $auth->getState());

        return 200;
    }
}