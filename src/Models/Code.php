<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Code extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'code';

    /**
     * @param \CarloNicora\Minimalism\Services\Auth\Auth $auth
     * @param Path $path
     * @param PositionedEncryptedParameter $userId
     * @param PositionedParameter|null $create
     * @return int
     * @throws Exception
     */
    public function get(
        \CarloNicora\Minimalism\Services\Auth\Auth $auth,
        Path $path,
        PositionedEncryptedParameter $userId,
        ?PositionedParameter $create,
    ): int
    {
        $user = $auth->getAuthenticationTable()->authenticateById($userId->getValue());

        $userResource = new ResourceObject('user', $userId->getEncryptedValue());
        $userResource->attributes->add('email', $user['email']);
        $userResource->attributes->add('new', ($create && $create->getValue()));

        $this->document->addResource($userResource);

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

        return 200;
    }
}