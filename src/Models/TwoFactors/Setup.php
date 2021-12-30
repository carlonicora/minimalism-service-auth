<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\TwoFactors;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\AppsTables;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\TokensTable;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use PHPGangsta_GoogleAuthenticator;
use RuntimeException;

class Setup extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'twofactorssetup';

    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param Path $path
     * @param string $client_id
     * @param string $state
     * @param string $token
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        Auth $auth,
        MySQL $mysql,
        Path $path,
        string $client_id,
        string $state,
        string $token,
    ): HttpCode
    {
        $isUser = false;

        $userId = $auth->validateToken(
            token: $token,
            isUser: $isUser,
        );

        if ($userId === null){
            throw new RuntimeException('Invalid token', HttpCode::Forbidden->value);
        }

        $authenticator = new PHPGangsta_GoogleAuthenticator();
        $salt = $authenticator->createSecret();

        $auth->setClientId($client_id);
        $auth->setState($state);
        $auth->setUserId($userId);
        $auth->setSalt($salt);

        /** @var TokensTable $tokensTable */
        $tokensTable = $mysql->create(dbReader: TokensTable::class);
        $currentToken = $tokensTable->loadByToken($token);

        /** @var AppsTables $appsTable */
        $appsTable = $mysql->create(dbReader: AppsTables::class);
        $app = $appsTable->readById($currentToken[0]['appId']);

        $user = $auth->getAuthenticationTable()->authenticateById($userId);

        $qrCodeUrl = $authenticator->getQRCodeGoogleUrl(
            name: $user['email'],
            secret: $salt,
            title: $app[0]['name'],
        );

        $this->document->links->add(
            new Link(
                name: 'qr',
                href: $qrCodeUrl,
            ),
        );

        $this->document->links->add(
            new Link(
                name: 'doSetupConfirmation',
                href: $path->getUrl() . 'TwoFactors/DoConfirmSetup',
            ),
        );

        return HttpCode::Ok;
    }
}