<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\LoggerInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth as AuthService;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\AppleIdsTable;
use CarloNicora\Minimalism\Services\Auth\Interfaces\AuthenticationInterface;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Apple extends AbstractAuthWebModel
{
    /**
     * @param LoggerInterface $logger
     * @param AuthService $auth
     * @param Path $path
     * @param MySQL $mysql
     * @param string|null $code
     * @param string|null $state
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        LoggerInterface $logger,
        AuthService     $auth,
        Path            $path,
        MySQL           $mysql,
        ?string         $code,
        ?string         $state,
    ): HttpCode
    {
        if($auth->getAppleState() !== $state) {
            $logger->error(
                message: 'Authorization server returned an invalid state parameter',
                domain: 'auth',
                context: [
                    'saved state' => $auth->getAppleState(),
                    'received state' => $state
                ]
            );
            die('Authorization server returned an invalid state parameter');
        }

        /** @var AppleIdsTable $appleIdsTable */
        $appleIdsTable = $mysql->create(AppleIdsTable::class);

        try {
            $response = $this->httpCall(
                [
                    'grant_type' => 'authorization_code',
                    'code' => $code ?? '',
                    'redirect_uri' => $path->getUrl() . 'apple',
                    'client_id' => $auth->getAppleClientId(),
                    'client_secret' => $auth->getAppleClientSecret(),
                ]
            );

            if (!isset($response['access_token'])) {
                $logger->error(
                    message: 'There has been an issue receiving the information from Apple',
                    domain: 'auth',
                    context: ['response' => $response]
                );
                header(
                    'location: '
                    . $path->getUrl()
                    . 'register?client_id=' . $auth->getClientId()
                    . '&state=' . $auth->getState()
                    . '&errorMessage=There has been an issue receiving the information from Apple'
                );
                exit;
            }

            $claims = explode('.', $response['id_token'])[1];
            $claims = json_decode(base64_decode($claims), true, 512, JSON_THROW_ON_ERROR);

            if (!array_key_exists('email', $claims)) {
                $appleIdRecord = $appleIdsTable->loadByAppleId($claims['sub']);

                if ($appleIdRecord === []){
                    $logger->error(
                        message: 'There has been an issue finding a user connected to your Apple account',
                        domain: 'auth',
                        context: ['apple id' => $claims['sub']]
                    );
                    header(
                        'location: '
                        . $path->getUrl()
                        . 'register?client_id=' . $auth->getClientId()
                        . '&state=' . $auth->getState()
                        . '&errorMessage=There has been an issue finding a user connected to your Apple account'
                    );
                    exit;
                }
                $appleIdRecord = $appleIdRecord[0];

                $user = $auth->getAuthenticationTable()->authenticateById($appleIdRecord['userId']);

                if ($user === null) {
                    $logger->error(
                        message: 'There has been an issue finding a user linked to your Apple account',
                        domain: 'auth',
                        context: ['user id' => $appleIdRecord['userId']]
                    );
                    header(
                        'location: '
                        . $path->getUrl()
                        . 'register?client_id=' . $auth->getClientId()
                        . '&state=' . $auth->getState()
                        . '&errorMessage=There has been an issue finding a user linked to your Apple account'
                    );
                    exit;
                }

                if ($user['isActive'] === AuthenticationInterface::INACTIVE_USER){
                    $auth->getAuthenticationTable()->activateUser($user);
                }
            } else {
                if (($user = $auth->getAuthenticationTable()->authenticateByEmail($claims['email'])) === null) {
                    $user = $auth->getAuthenticationTable()->generateNewUser($claims['email'], ($claims['name'] ?? null), 'apple');
                    $auth->setIsNewRegistration();
                }

                if (!empty($user)){
                    if (!array_key_exists('isActive', $user) || $user['isActive'] === AuthenticationInterface::INACTIVE_USER) {
                        $auth->getAuthenticationTable()->activateUser($user);
                    }

                    $appleIdRecord = $appleIdsTable->loadByAppleId($claims['sub']);

                    if ($appleIdRecord === []){
                        $appleIdRecord = [
                            'appleId' => $claims['sub'],
                            'userId' => $user['userId']
                        ];
                        $appleIdsTable->update($appleIdRecord);
                    }
                }
            }

            $auth->setUserId($user['userId']);

            header(
                'location: '
                . $path->getUrl()
                . 'auth?client_id=' . $auth->getClientId() . '&state=' . $auth->getState());
        } catch (Exception $e) {
            $logger->error(
                message: 'Error in Apple login',
                domain: 'auth',
                context: ['exception' => $e]);
            echo 'error';
        }

        return HttpCode::Ok;
    }

    /**
     * @param array $params
     * @return array
     * @throws Exception
     */
    private function httpCall(array $params): array
    {
        $ch = curl_init('https://appleid.apple.com/auth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: curl',
        ]);

        $response = curl_exec($ch);

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}