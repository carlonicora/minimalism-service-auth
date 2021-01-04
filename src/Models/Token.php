<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\AppsTables;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\AuthsTable;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\TokensTable;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use DateTime;
use Exception;
use JsonException;
use RuntimeException;

class Token extends AbstractAuthWebModel
{
    /**
     * @param MySQL $mysql
     * @param string $grantType
     * @param string|null $code
     * @param string $client_id
     * @return int
     * @throws JsonException|Exception
     */
    public function post(
        MySQL $mysql,
        string $grantType,
        ?string $code,
        string $client_id,
    ): int
    {
        /** @noinspection NotOptimalIfConditionsInspection */
        if (!(
            strtolower($grantType) === 'authorization_code'
            ||
            strtolower($grantType) === 'client_credentials')
        ){
            throw new RuntimeException('grant_type not supported', 500);
        }

        header("Access-Control-Allow-Origin: *");

        $response = [
            'access_token' => '',
            'token_type' => 'bearer'
        ];

        if (strtolower($grantType) === 'authorization_code') {
            /** @var AuthsTable $auths */
            $auths = $mysql->create(AuthsTable::class);
            $auth = $auths->loadByCode($code);

            if (new DateTime($auth['expiration']) < new DateTime()) {
                throw new RuntimeException('The authorization code is incorrect or expired', 412);
            }

            $token = [
                'appId' => $auth['appId'],
                'userId' => $auth['userId'],
                'isUser' => true,
                'token' => bin2hex(random_bytes(32))
            ];
        } else {
            /** @var AppsTables $apps */
            $apps = $mysql->create(AppsTables::class);

            $app = $apps->getByClientId($client_id);

            $token = [
                'appId' => $app['appId'],
                'userId' => (int)(microtime(true)*1000),
                'isUser' => false,
                'token' => bin2hex(random_bytes(32))
            ];
        }

        $mysql->create(TokensTable::class)->update($token);

        $response['access_token'] = $token['token'];

        header("Access-Control-Allow-Origin: *");

        echo json_encode($response, JSON_THROW_ON_ERROR);
        return 201;
    }
}