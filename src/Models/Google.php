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
        $client->setAuthConfig($this->auth->getGoogleIdentityFile());
        $client->setRedirectUri($this->services->paths()->getUrl() . 'google');
        $client->addScope('email');
        $client->addScope('profile');

        $token = $client->fetchAccessTokenWithAuthCode($this->googleCode);
        $token_data = $client->verifyIdToken();

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}