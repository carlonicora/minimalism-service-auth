<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Change extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'reset';

    /**
     * @param \CarloNicora\Minimalism\Services\Auth\Auth $auth
     * @param Path $path
     * @param PositionedEncryptedParameter $userId
     * @return int
     * @throws Exception
     */
    public function get(
        \CarloNicora\Minimalism\Services\Auth\Auth $auth,
        Path $path,
        PositionedEncryptedParameter $userId,
    ): int
    {
        $this->document->addResource(new ResourceObject('user', $userId->getEncryptedValue()));

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

        return 200;
    }
}