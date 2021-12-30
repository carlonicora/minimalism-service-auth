<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth as AuthService;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use Google_Client;

class Google extends AbstractAuthWebModel
{
    /**
     * @param AuthService $auth
     * @param Path $path
     * @param string|null $code
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        AuthService $auth,
        Path        $path,
        ?string     $code,
    ): HttpCode
    {
        $client = new Google_Client();
        $client->setAuthConfig($path->getRoot() . DIRECTORY_SEPARATOR . $auth->getGoogleIdentityFile());
        $client->setRedirectUri($path->getUrl() . 'google');
        $client->addScope('email');
        $client->addScope('profile');

        /** @noinspection UnusedFunctionResultInspection */
        $client->fetchAccessTokenWithAuthCode($code);
        $token_data = $client->verifyIdToken();

        if (!array_key_exists('email', $token_data)){
            header(
                'location: '
                . $path->getUrl()
                . 'auth?client_id=' . $auth->getClientId()
                . '&state=' . $auth->getState()
                . '&errorMessage=The social account does not have a valid email address'
            );
            exit;
        }

        if (($user = $auth->getAuthenticationTable()->authenticateByEmail($token_data['email'])) === null) {
            $user = $auth->getAuthenticationTable()->generateNewUser($token_data['email'], $token_data['name'], 'google');
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