<?php
namespace OldModels;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Password extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'password';

    /**
     * @param Path $path
     * @param PositionedEncryptedParameter $userId
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        Path $path,
        PositionedEncryptedParameter $userId,
    ): HttpCode
    {
        $this->document->meta->add('userId', $userId->getEncryptedValue());

        $this->document->links->add(
            new Link('doLogin', $path->getUrl() . 'Login/Dopasswordlogin')
        );

        $this->document->links->add(
            new Link('doCodeLogin', $path->getUrl() . 'Accounts/Doaccountlookup/' . $userId->getEncryptedValue() . '?overridePassword=true')
        );

        $this->document->links->add(
            new Link('forgot', $path->getUrl() . 'forgot')
        );

        return HttpCode::Ok;
    }
}