<?php
namespace OldModels;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\LoggerInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth as AuthService;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Apple extends AbstractAuthWebModel
{
    /**
     * @param AuthService $auth
     * @param Path $path
     * @param MySQL $mysql
     * @param string|null $code
     * @param string|null $state
     * @param LoggerInterface|null $logger
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        AuthService     $auth,
        Path            $path,
        MySQL           $mysql,
        ?string         $code,
        ?string         $state,
        ?LoggerInterface $logger=null,
    ): HttpCode
    {
        if (!array_key_exists('email', $claims)) {
            $appleIdRecord = $appleIdsTable->loadByAppleId($claims['sub']);



            $user = $auth->getAuthenticationTable()->authenticateById($appleIdRecord['userId']);

            if ($user === null) {
                $logger?->error(
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

            if (!$user->isActive()){
                $auth->getAuthenticationTable()->activateUser($user);
            }
        }

        return HttpCode::Ok;
    }
}