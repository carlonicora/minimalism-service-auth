<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\AppleIdsTable;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Apple extends AbstractAuthWebModel
{
    /**
     * @param \CarloNicora\Minimalism\Services\Auth\Auth $auth
     * @param Path $path
     * @param MySQL $mysql
     * @param string|null $code
     * @param string|null $state
     * @return int
     * @throws Exception
     */
    public function post(
        \CarloNicora\Minimalism\Services\Auth\Auth $auth,
        Path $path,
        MySQL $mysql,
        ?string $code,
        ?string $state,
    ): int
    {
        if($_SESSION['state'] !== $state) {
            die('Authorization server returned an invalid state parameter');
        }

        /** @var AppleIdsTable $appleIdsTable */
        $appleIdsTable = $mysql->create(AppleIdsTable::class);

        try {
            $response = $this->httpCall(
                'https://appleid.apple.com/auth/token',
                [
                    'grant_type' => 'authorization_code',
                    'code' => $code ?? '',
                    'redirect_uri' => $path->getUrl() . 'apple',
                    'client_id' => $auth->getAppleClientId(),
                    'client_secret' => $auth->getAppleClientSecret(),
                ]
            );

            if (!isset($response['access_token'])) {
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
                try {
                    $appleIdRecord = $appleIdsTable->loadByAppleId($claims['sub']);

                    $user = $auth->getAuthenticationTable()->authenticateById($appleIdRecord['userId']);

                    if ($user === null) {
                        header(
                            'location: '
                            . $path->getUrl()
                            . 'register?client_id=' . $auth->getClientId()
                            . '&state=' . $auth->getState()
                            . '&errorMessage=There has been an issue finding a user linked to your Apple account'
                        );
                        exit;
                    }

                    if ($user['isActive'] === false){
                        $auth->getAuthenticationTable()->activateUser($user);
                    }
                } catch (RecordNotFoundException) {
                    header(
                        'location: '
                        . $path->getUrl()
                        . 'register?client_id=' . $auth->getClientId()
                        . '&state=' . $auth->getState()
                        . '&errorMessage=There has been an issue finding a user connected to your Apple account'
                    );
                    exit;
                }
            } else {
                if (($user = $auth->getAuthenticationTable()->authenticateByEmail($claims['email'])) === null) {
                    $user = $auth->getAuthenticationTable()->generateNewUser($claims['email'], ($claims['name'] ?? null), 'apple');
                    $auth->setIsNewRegistration();
                }

                if (!empty($user)){
                    if (!array_key_exists('isActive', $user) || $user['isActive'] === false) {
                        $auth->getAuthenticationTable()->activateUser($user);
                    }

                    try {
                        $appleIdsTable->loadByAppleId($claims['sub']);
                    } catch (RecordNotFoundException) {
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
        } catch (Exception) {
            echo 'error';
        }

        return 200;
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