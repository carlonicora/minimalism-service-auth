<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Login;

use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Mailer\Interfaces\MailerInterface;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Factories\CodeFactory;
use CarloNicora\Minimalism\Services\Auth\Interfaces\AuthenticationInterface;
use CarloNicora\Minimalism\Services\Auth\Models\Auth as AuthService;
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
     * @param MailerInterface $mailer
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
        MailerInterface $mailer,
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

         $this->authenticateAndValidateUser(
             auth: $auth,
             mysql: $mysql,
             encrypter: $encrypter,
             path: $path,
             mailer: $mailer,
             userIdInt: $userIdInt,
             code: $code,
        );

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
     * @param MailerInterface $mailer
     * @param int $userIdInt
     * @param int $code
     * @return void
     * @throws Exception
     */
    private function authenticateAndValidateUser(
        Auth $auth,
        MySQL $mysql,
        EncrypterInterface $encrypter,
        Path $path,
        MailerInterface $mailer,
        int $userIdInt,
        int $code,
    ): void
    {
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

        if ($user['isActive'] === AuthenticationInterface::INACTIVE_USER) {
            $auth->getAuthenticationTable()->activateUser($user);
        }

        $auth->setUserId($user['userId']);
    }

    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param EncrypterInterface $encrypter
     * @param Path $path
     * @param MailerInterface $mailer
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
        MailerInterface $mailer,
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

        $this->authenticateAndValidateUser(
            auth: $auth,
            mysql: $mysql,
            encrypter: $encrypter,
            path: $path,
            mailer: $mailer,
            userIdInt: $userIdInt,
            code: $code,
        );

        $this->redirection = AuthService::class;
        $this->redirectionParameters = [
            'named' => [
                'client_id' => $client_id->getValue(),
                'state' => $state->getValue()
            ]
        ];
        return 302;
    }
}