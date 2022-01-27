<?php
namespace OldModels;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth as AuthService;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;

class Code extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'code';

    /**
     * @param AuthService $auth
     * @param Path $path
     * @param PositionedEncryptedParameter $userId
     * @param PositionedParameter|null $create
     * @param PositionedParameter|null $clientId
     * @param PositionedParameter|null $state
     * @param PositionedParameter|null $code
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        AuthService $auth,
        Path $path,
        PositionedEncryptedParameter $userId,
        ?PositionedParameter $create,
        ?PositionedParameter $clientId,
        ?PositionedParameter $state,
        ?PositionedParameter $code,
    ): HttpCode
    {
        $user = $auth->getAuthenticationTable()->authenticateById($userId->getValue());

        if ($user === null){
            throw new RuntimeException('missing user details', 500);
        }

        if ($clientId !== null){
            $auth->setClientId($clientId->getValue());
        }

        if ($state !== null){
            $auth->setState($state->getValue());
        }

        $userResource = new ResourceObject('user', $userId->getEncryptedValue());
        $userResource->attributes->add('email', $user->getEmail());
        $userResource->attributes->add('new', ($create && $create->getValue()));

        $this->document->addResource($userResource);
        if ($code !== null) {
            $codeElements = str_split($code->getValue());

            $digit = 0;
            foreach ($codeElements as $codeElement) {
                $digit++;
                $this->document->meta->add(name: 'code' . $digit, value: $codeElement);
            }
        }

        $this->document->links->add(
            new Link('doLogin', $path->getUrl() . 'Login/Docodelogin')
        );

        $this->document->links->add(
            new Link('doCodeLogin',
                $path->getUrl()
                . 'Accounts/Doaccountlookup/'
                . $userId->getEncryptedValue()
                . '?overridePassword=true')
        );

        return HttpCode::Ok;
    }
}