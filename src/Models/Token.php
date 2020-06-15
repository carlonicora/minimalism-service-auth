<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\AuthsTable;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\TokensTable;
use CarloNicora\Minimalism\Services\Auth\Events\AuthErrorEvents;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use DateTime;
use Exception;
use JsonException;
use RuntimeException;

class Token extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $grantType=null;

    /** @var string|null  */
    protected ?string $code=null;

    /** @var string|null  */
    protected ?string $clientId=null;

    /** @var array  */
    protected array $parameters = [
        'grant_type' => ['name' => 'grantType', 'required' => true, 'validator' => ParameterValidator::PARAMETER_TYPE_STRING],
        'code' => ['required' => true, 'validator' => ParameterValidator::PARAMETER_TYPE_STRING],
        'client_id' => ['name' => 'clientId', 'required' => true, 'validator' => ParameterValidator::PARAMETER_TYPE_STRING]
    ];

    /**
     * @return ResponseInterface
     * @throws JsonException|Exception
     */
    public function generateData(): ResponseInterface
    {
        if (strtolower($this->grantType) !== 'authorization_code') {
            throw new RuntimeException('grant_type not supported', 500);
        }

        $response = [
            'access_token' => '',
            'token_type' => 'bearer'
        ];

        /** @var AuthsTable $auths */
        $auths = $this->mysql->create(AuthsTable::class);
        $auth = $auths->loadByCode($this->code);

        if (new DateTime($auth['expiration']) < new DateTime()) {
            $this->services->logger()->error()->log(
                AuthErrorEvents::AUTH_CODE_EXPIRED()
            )->throw();
        }

        $token = [
            'appId' => $auth['appId'],
            'userId' => $auth['userId'],
            'token' => bin2hex(random_bytes(32))
        ];

        $this->mysql->create(TokensTable::class)->update($token);

        $response['access_token'] = $token['token'];

        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }
}