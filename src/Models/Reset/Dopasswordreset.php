<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Reset;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Mailer\Interfaces\MailerInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Factories\CodeFactory;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Dopasswordreset extends AbstractAuthWebModel
{
    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param EncrypterInterface $encrypter
     * @param Path $path
     * @param MailerInterface $mailer
     * @param EncryptedParameter $userId
     * @param string $code
     * @param string $password
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        Auth $auth,
        MySQL $mysql,
        EncrypterInterface $encrypter,
        Path $path,
        MailerInterface $mailer,
        EncryptedParameter $userId,
        string $code,
        string $password,
    ): HttpCode
    {
        $user = $auth->getAuthenticationTable()->authenticateById($userId->getValue());
        if ($user === null) {
            throw new RuntimeException('Invalid email or password', 401);
        }

        $codeFactory = new CodeFactory(
            auth: $auth,
            mysql: $mysql,
            encrypter: $encrypter,
            path: $path,
            mailer: $mailer,
        );

        $codeFactory->validateCode(
            user: $user,
            code: $code,
        );

        $auth->getAuthenticationTable()->updatePassword($userId->getValue(), $password);

        $auth->setUserId($userId->getValue());

        $this->document->meta->add(
            'redirection',
            $path->getUrl() . 'auth'
        );

        return HttpCode::Ok;
    }
}