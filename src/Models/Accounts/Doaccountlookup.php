<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Accounts;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Mailer\Interfaces\MailerInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Factories\CodeFactory;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Doaccountlookup extends AbstractAuthWebModel
{
    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param EncrypterInterface $encrypter
     * @param Path $path
     * @param MailerInterface $mailer
     * @param PositionedEncryptedParameter|null $userId
     * @param string|null $email
     * @param bool|null $create
     * @param bool|null $recoverPassword
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        Auth $auth,
        MySQL $mysql,
        EncrypterInterface $encrypter,
        Path $path,
        MailerInterface $mailer,
        ?PositionedEncryptedParameter $userId=null,
        ?string $email=null,
        ?bool $create=false,
        ?bool $recoverPassword=false,
    ): HttpCode
    {
        $codeFactory = new CodeFactory(
            auth: $auth,
            mysql: $mysql,
            encrypter: $encrypter,
            path: $path,
            mailer: $mailer,
        );

        $user = [];

        if ($auth->getUserId() !== null) {
            $user = $auth->getAuthenticationTable()->authenticateById($auth->getUserId());
        } elseif ($create === false && $email !== null && ($user = $auth->getAuthenticationTable()->authenticateByEmail($email)) === null) {
            throw new RuntimeException('Could not find your account', 401);
        } elseif ($userId !== null &&  ($user = $auth->getAuthenticationTable()->authenticateById($userId->getValue())) === null) {
            throw new RuntimeException('Could not find your account', 401);
        } elseif ($create === true && ($user = $auth->getAuthenticationTable()->authenticateByEmail($email)) === null) {
            $user = $auth->getAuthenticationTable()->generateNewUser($email);
            $auth->setIsNewRegistration();
        }

        if ($recoverPassword){
            $codeFactory->generateAndSendResetCode($user);

            $this->document->meta->add(
                'message',
                'If your email is in our database, we have sent you a message to reset your password'
            );
        } else {
            if (empty($user['password'])) {
                $codeFactory->generateAndSendCode($user);

                if ($create){
                    $redirection = 'code/' . $encrypter->encryptId($user['userId']) . '/1';
                } else {
                    $redirection = 'code/' . $encrypter->encryptId($user['userId']);
                }
            } else {
                $redirection = 'password/' . $encrypter->encryptId($user['userId']);
            }

            $this->document->meta->add(
                'userId',
                $encrypter->encryptId($user['userId']),
            );

            $this->document->meta->add(
                'redirection',
                $path->getUrl() . $redirection
            );
        }

        return HttpCode::Ok;
    }
    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param EncrypterInterface $encrypter
     * @param Path $path
     * @param MailerInterface $mailer
     * @param PositionedEncryptedParameter|null $userId
     * @param bool|null $overridePassword
     * @param bool|null $recoverPassword
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        Auth $auth,
        MySQL $mysql,
        EncrypterInterface $encrypter,
        Path $path,
        MailerInterface $mailer,
        ?PositionedEncryptedParameter $userId=null,
        ?bool $overridePassword=false,
        ?bool $recoverPassword=false,
    ): HttpCode
    {
        $codeFactory = new CodeFactory(
            auth: $auth,
            mysql: $mysql,
            encrypter: $encrypter,
            path: $path,
            mailer: $mailer,
        );

        $user = [];

        if ($auth->getUserId() !== null) {
            $user = $auth->getAuthenticationTable()->authenticateById($auth->getUserId());
        } elseif ($userId !== null &&  ($user = $auth->getAuthenticationTable()->authenticateById($userId->getValue())) === null) {
            throw new RuntimeException('Could not find your account', 401);
        }

        if ($recoverPassword){
            $codeFactory->generateAndSendResetCode($user);

            $this->document->meta->add(
                'message',
                'If your email is in our database, we have sent you a message to reset your password'
            );
        } else {
            $redirection = null;

            if ($overridePassword || empty($user['password'])) {
                $codeFactory->generateAndSendCode($user);

                if ($overridePassword){
                    header(
                        'location:'
                        . $path->getUrl()
                        . 'code/' . $encrypter->encryptId($user['userId'])
                    );
                } else {
                    $redirection = 'code/' . $encrypter->encryptId($user['userId']);
                }
            } else {
                $redirection = 'password/' . $encrypter->encryptId($user['userId']);
            }

            if ($redirection !== null) {
                $this->document->meta->add(
                    'redirection',
                    $path->getUrl() . $redirection
                );
            }
        }

        return HttpCode::Ok;
    }
}