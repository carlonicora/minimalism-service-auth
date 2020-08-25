<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;
use Facebook\Authentication\OAuth2Client;
use Facebook\FacebookApp;
use Facebook\FacebookClient;
use Facebook\Helpers\FacebookRedirectLoginHelper;

class Facebook extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $facebookCode=null;

    /** @var string|null  */
    protected ?string $facebookState=null;

    /** @var array|array[]  */
    protected array $parameters = [
        'code' => [
            ParameterInterface::NAME => 'facebookCode',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        'state' => [
            ParameterInterface::NAME => 'facebookState',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ]
    ];

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $app = new FacebookApp(
            $this->auth->getFacebookId(),
            $this->auth->getFacebookSecret()
        );
        $client = new FacebookClient();
        $oAuth = new OAuth2Client($app, $client);

        $helper = new FacebookRedirectLoginHelper($oAuth);

        $accessToken = $helper->getAccessToken($this->services->paths()->getUrl() . 'facebook');

        $fb = new \Facebook\Facebook([
            'app_id' => $this->auth->getFacebookId(),
            'app_secret' => $this->auth->getFacebookSecret(),
            'default_graph_version' => 'v5.0',
        ]);

        $facebookUser = $fb->get('/me?&fields=name,email,picture', $accessToken);
        $fbu = $facebookUser->getDecodedBody();

        if (!array_key_exists('email', $fbu) || empty($fbu['email'])){
            header(
                'location: '
                . $this->services->paths()->getUrl()
                . 'register?client_id=' . $this->auth->getClientId()
                . '&state=' . $this->auth->getState()
                . '&errorMessage=The social account does not have a valid email address'
            );
            exit;
        }

        if (($user = $this->auth->getAuthenticationTable()->authenticateByEmail($fbu['email'])) === null) {
            $user = $this->auth->getAuthenticationTable()->generateNewUser($fbu['email'], $fbu['name'], 'facebook');
            $this->auth->getAuthenticationTable()->activateUser($user);
        } elseif ($user['isActive'] === false){
            $this->auth->getAuthenticationTable()->activateUser($user);
        }

        $this->auth->setUserId($user['userId']);

        header(
            'location: '
            . $this->services->paths()->getUrl()
            . 'auth?client_id=' . $this->auth->getClientId() . '&state=' . $this->auth->getState());

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}