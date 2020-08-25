<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;
use RuntimeException;

class Apple extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $code=null;

    /** @var string|null  */
    protected ?string $state=null;

    /** @var array|array[]  */
    protected array $parameters = [
        'code' => [
            ParameterInterface::NAME => 'code',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        'state' => [
            ParameterInterface::NAME => 'state',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ]
    ];

    /**
     * @throws Exception
     */
    public function preRender(): void
    {
        parent::preRender();

        if($_SESSION['state'] !== $this->state) {
            die('Authorization server returned an invalid state parameter');
        }
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        try {
            $response = $this->httpCall(
                'https://appleid.apple.com/auth/token',
                [
                    'grant_type' => 'authorization_code',
                    'code' => $this->code,
                    'redirect_uri' => $this->services->paths()->getUrl() . 'apple',
                    'client_id' => $this->auth->getAppleClientId(),
                    'client_secret' => $this->auth->getAppleClientSecret(),
                ]
            );

            if (!isset($response['access_token'])) {
                throw new RuntimeException('error');
            }

            $claims = explode('.', $response['id_token'])[1];
            $claims = json_decode(base64_decode($claims), true, 512, JSON_THROW_ON_ERROR);

            if (($user = $this->auth->getAuthenticationTable()->authenticateByEmail($claims['email'])) === null) {
                $user = $this->auth->getAuthenticationTable()->generateNewUser($claims['email'], $claims['name'], 'apple');
                $this->auth->getAuthenticationTable()->activateUser($user);
            } elseif ($user['isActive'] === false){
                $this->auth->getAuthenticationTable()->activateUser($user);
            }

            $this->auth->setUserId($user['userId']);

            header(
                'location: '
                . $this->services->paths()->getUrl()
                . 'auth?client_id=' . $this->auth->getClientId() . '&state=' . $this->auth->getState());
        } catch (Exception $e) {
            echo 'error';
        }

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }

    /**
     * @param $url
     * @param false $params
     * @return array
     * @throws Exception
     */
    private function httpCall($url, $params=false): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($params) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: curl',
        ]);

        $response = curl_exec($ch);

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}