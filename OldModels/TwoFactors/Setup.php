<?php
namespace OldModels\TwoFactors;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\OAuth\OAuth;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

class Setup extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'twofactorssetup';

    /**
     * @param OAuth $OAuth
     * @param Auth $auth
     * @param Path $path
     * @param string $client_id
     * @param string $state
     * @param string $token
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        OAuth $OAuth,
        Auth $auth,
        Path $path,
        string $client_id,
        string $state,
        string $token,
    ): HttpCode
    {
        if ($OAuth->getUserId() === null){
            throw new RuntimeException('Invalid token', HttpCode::Forbidden->value);
        }

        $salt = (new GoogleAuthenticator())->generateSecret();

        $auth->setClientId($client_id);
        $auth->setState($state);
        $auth->setUserId($OAuth->isUser());
        $auth->setSalt($salt);

        $OAuth->loadToken($token);
        $app = $OAuth->getApp();

        $user = $auth->getAuthenticationTable()->authenticateById($OAuth->getUserId());

        if ($user === null){
            throw new RuntimeException('missing user details', 500);
        }

        $qrCodeUrl = GoogleQrUrl::generate(
            accountName:$user->getEmail(),
            secret: $salt,
            issuer:$app->getName(),
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