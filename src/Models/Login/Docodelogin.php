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
     * @param EncryptedParameter|null $userId
     * @param string|null $code
     * @return int
     * @throws Exception
     */
    public function post(
        Auth $auth,
        MySQL $mysql,
        EncrypterInterface $encrypter,
        Path $path,
        Mailer $mailer,
        ?PositionedEncryptedParameter $userIdLink,
        ?PositionedParameter $codeLink=null,
        ?EncryptedParameter $userId=null,
        ?string $code=null,
    ): int
    {
        if ($code === null && $codeLink !== null){
            $code = $codeLink->getValue();
        }
        if ($userId !== null) {
            $userIdInt = $userId->getValue();
        } else {
            $userIdInt = $userIdLink?->getValue();
        }

        if (($user = $auth->getAuthenticationTable()->authenticateById($userIdInt)) === null){
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

        $this->document->meta->add(
            'redirection',
            $path->getUrl() . 'auth'
        );

        return 200;
    }

    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param EncrypterInterface $encrypter
     * @param Path $path
     * @param Mailer $mailer
     * @param PositionedEncryptedParameter $userIdLink
     * @param PositionedParameter $codeLink
     * @param PositionedParameter $client_id
     * @param PositionedParameter $state
     * @return int
     * @throws Exception
     */
    public function get(
        Auth $auth,
        MySQL $mysql,
        EncrypterInterface $encrypter,
        Path $path,
        Mailer $mailer,
        PositionedEncryptedParameter $userIdLink,
        PositionedParameter $codeLink,
        PositionedParameter $client_id,
        PositionedParameter $state,
    ): int
    {
        $auth->setClientId($client_id->getValue());
        $auth->setState($state->getValue());
        $code = $codeLink->getValue();
        $userIdInt = $userIdLink->getValue();

        if (($user = $auth->getAuthenticationTable()->authenticateById($userIdInt)) === null){
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

        $this->redirection = \CarloNicora\Minimalism\Services\Auth\Models\Auth::class;
        $this->redirectionParameters = [
            'named' => [
                'client_id' => $client_id->getValue(),
                'state' => $state->getValue()
            ]
        ];
        return 302;
    }
}