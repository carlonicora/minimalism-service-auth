<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Login;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\EncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Mailer\Interfaces\MailerInterface;
use CarloNicora\Minimalism\Objects\ModelParameters;
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
     * @return HttpCode
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
    ): HttpCode
    {
        if ($code === null && $codeLink !== null){
            $code = $codeLink->getValue();
        }

        if ($userId !== null) {
            $userIdInt = $userId->getValue();
        } else {
            $userIdInt = $userIdLink?->getValue();
        }

         $user = $this->authenticateAndValidateUser(
             auth: $auth,
             mysql: $mysql,
             encrypter: $encrypter,
             path: $path,
             mailer: $mailer,
             userIdInt: $userIdInt,
             code: $code,
        );

        if ($user['salt'] === null) {
            $this->document->meta->add(
                'redirection',
                $path->getUrl() . 'auth'
            );
        } else {
            $this->document->meta->add(
                'redirection',
                $path->getUrl() . 'TwoFactors/validation'
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
     * @param int $userIdInt
     * @param int $code
     * @return array
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
    ): array
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

        return $user;
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
     * @return HttpCode
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
    ): HttpCode
    {
        $auth->setClientId($client_id->getValue());
        $auth->setState($state->getValue());
        $code = $codeLink->getValue();
        $userIdInt = $userIdLink->getValue();

        /** @noinspection UnusedFunctionResultInspection */
        $this->authenticateAndValidateUser(
            auth: $auth,
            mysql: $mysql,
            encrypter: $encrypter,
            path: $path,
            mailer: $mailer,
            userIdInt: $userIdInt,
            code: $code,
        );

        $modelParameters = new ModelParameters();
        $modelParameters->addNamedParameter(
            name: 'client_id',
            value: $client_id->getValue(),
        );
        $modelParameters->addNamedParameter(
            name: 'state',
            value: $state->getValue(),
        );

        return $this->redirect(
            modelClass: AuthService::class,
            parameters: $modelParameters,
        );
    }
}