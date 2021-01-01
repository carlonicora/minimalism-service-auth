<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Login;

use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Factories\CodeFactory;
use CarloNicora\Minimalism\Services\Mailer\Mailer;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Docodelogin extends AbstractAuthWebModel
{

    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param EncrypterInterface $encrypter
     * @param Path $path
     * @param Mailer $mailer
     * @param PositionedEncryptedParameter|null $userIdLink
     * @param PositionedParameter|null $codeLink
     * @param PositionedParameter|null $clientId
     * @param PositionedParameter|null $state
     * @param EncryptedParameter|null $userIdForm
     * @param int|null $codeForm
     * @return int
     * @throws Exception
     */
    public function get(
        Auth $auth,
        MySQL $mysql,
        EncrypterInterface $encrypter,
        Path $path,
        Mailer $mailer,
        ?PositionedEncryptedParameter $userIdLink,
        ?PositionedParameter $codeLink,
        ?PositionedParameter $clientId,
        ?PositionedParameter $state,
        ?EncryptedParameter $userIdForm,
        ?int $codeForm,
    ): int
    {
        $code = $codeForm ?? $codeLink ? $codeLink->getValue() : null;
        if ($userIdForm !== null) {
            $userId = $userIdForm->getValue();
        } else {
            $userId = $userIdLink ? $userIdLink->getValue() : null;
        }

        if (($user = $auth->getAuthenticationTable()->authenticateById($userId)) === null){
            throw new RuntimeException('Could not find your account', 401);
        }

        $codeFactory = new CodeFactory(
            auth: $auth,
            mysql: $mysql,
            encrypter: $encrypter,
            path: $path,
            mailer: $mailer,
        );

        $codeFactory->validateCode($user, $code);

        if (!$user['isActive']) {
            $auth->getAuthenticationTable()->activateUser($user);
        }

        $auth->setUserId($user['userId']);

        if ($userIdForm !== null) {
            $this->document->meta->add(
                'redirection',
                $path->getUrl() . 'auth'
            );
        } else {
            header(
                'location: '
                . $path->getUrl()
                . 'auth?client_id=' . ($clientId ? $clientId->getValue() : '') . '&state=' . ($state ? $state->getValue() : ''));
        }

        return 200;
    }
}