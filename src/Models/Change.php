<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\Mailer\Interfaces\MailerInterface;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Factories\CodeFactory;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Change extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'reset';

    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param EncrypterInterface $encrypter
     * @param Path $path
     * @param MailerInterface $mailer
     * @param PositionedEncryptedParameter $userId
     * @param PositionedParameter $code
     * @param PositionedParameter $clientId
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
        PositionedEncryptedParameter $userId,
        PositionedParameter $code,
        PositionedParameter $clientId,
        PositionedParameter $state,
    ): HttpCode
    {
        $user = $auth->getAuthenticationTable()->authenticateById($userId->getValue());

        if ($user === null){
            throw new RuntimeException('missing user details', 500);
        }
        
        $codeFactory = new CodeFactory(
            auth: $auth,
            mysql: $mysql,
            encrypter: $encrypter,
            path: $path,
            mailer: $mailer,
        );
        
        $validCode = $codeFactory->isCodeValid(
            user: $user,
            code: $code->getValue(),
        );

        $this->document->meta->add(name: 'validCode', value: $validCode);

        if ($validCode) {
            $auth->setClientId($clientId->getValue());
            $auth->setState($state->getValue());
            $auth->setUserId($userId->getValue());

            $this->document->addResource(new ResourceObject('user', $userId->getEncryptedValue()));

            $this->document->meta->add(name: 'code', value: $code->getValue());

            $this->document->links->add(
                new Link('doReset', $path->getUrl() . 'Reset/Dopasswordreset')
            );

            try {
                $app = $auth->getAppByClientId();

                if ($app !== []) {
                    $this->document->links->add(
                        new Link('doCancel', $app[0]['url'])
                    );
                }
            } catch (Exception) {
            }
        } else {
            $this->document->links->add(
                new Link('doCancel', $path->getUrl() . 'auth?client_id=' . $clientId->getValue() . '&state=' . $state->getValue())
            );
        }

        return HttpCode::Ok;
    }
}