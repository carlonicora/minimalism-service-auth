<?php
namespace CarloNicora\Minimalism\Services\Auth\Abstracts;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Factories\MinimalismFactories;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\OAuth\IO\AppIO;
use Exception;

class AbstractAuthWebModel extends AbstractModel
{
    /** @var Auth  */
    protected Auth $auth;

    /** @var string  */
    protected string $url;

    /**
     * AbstractAuthWebModel constructor.
     * @param MinimalismFactories $minimalismFactories
     * @param string|null $function
     * @throws Exception
     */
    public function __construct(
        MinimalismFactories $minimalismFactories,
        ?string $function=null,
    )
    {
        parent::__construct(
            $minimalismFactories,
            $function
        );

        $this->url = $minimalismFactories->getServiceFactory()->getPath()->getUrl();
        $this->auth = $minimalismFactories->getServiceFactory()->create(Auth::class);

        $this->document->links->add(
            new Link('home', $this->url)
        );
    }

    /**
     * @param bool $redirectImmediately
     * @return void
     * @throws Exception
     */
    protected function addCorrectRedirection(
        bool $redirectImmediately = false,
    ): void
    {
        $app = $this->objectFactory->create(AppIO::class)->readByClientId($this->auth->getClientId());

        if ($redirectImmediately){
            header('Location:' . $this->url . ($app->isTrusted() ? 'redirect' :'auth'));
            exit;
        }

        $this->document->links->add(
            new Link(
                name: 'redirect',
                href: $this->url . ($app->isTrusted() ? 'redirect' :'auth'),
            ),
        );
    }
}