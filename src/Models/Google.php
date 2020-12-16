<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;
use Google_Client;

class Google extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $googleCode=null;

    /** @var array|array[]  */
    protected array $parameters = [
        'code' => [
            ParameterInterface::NAME => 'googleCode',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ],
    ];
    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $client = new Google_Client();
        $client->setAuthConfig($this->services->paths()->getRoot() . DIRECTORY_SEPARATOR . $this->auth->getGoogleIdentityFile());
        $client->setRedirectUri($this->services->paths()->getUrl() . 'google');
        $client->addScope('email');
        $client->addScope('profile');

        $client->fetchAccessTokenWithAuthCode($this->googleCode);
        $token_data = $client->verifyIdToken();

        if (!array_key_exists('email', $token_data)){
            header(
                'location: '
                . $this->services->paths()->getUrl()
                . 'auth?client_id=' . $this->auth->getClientId()
                . '&state=' . $this->auth->getState()
                . '&errorMessage=The social account does not have a valid email address'
            );
            exit;
        }

        if (($user = $this->auth->getAuthenticationTable()->authenticateByEmail($token_data['email'])) === null) {
            $user = $this->auth->getAuthenticationTable()->generateNewUser($token_data['email'], $token_data['name'], 'google');
            $this->auth->setIsNewRegistration();
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