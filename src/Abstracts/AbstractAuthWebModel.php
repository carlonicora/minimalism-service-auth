<?php
namespace CarloNicora\Minimalism\Services\Auth\Abstracts;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\Meta;
use CarloNicora\Minimalism\Factories\MinimalismFactories;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Cancel;
use CarloNicora\Minimalism\Services\OAuth\OAuth;
use Exception;

class AbstractAuthWebModel extends AbstractAuthModel
{
    /** @var OAuth  */
    protected OAuth $oauth;
    
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

        $this->oauth = $minimalismFactories->getServiceFactory()->create(OAuth::class);

        $this->resetAuthLink();

        $this->document->links->add(
            new Link(
                name: 'home',
                href: $this->url,
            ),
        );

        $this->document->links->add(
            new Link(
                name: 'forgot',
                href: $this->url . 'auth/forgot',
            ),
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function resetAuthLink(
    ): void
    {
        if ($this->document->links->has('auth')){
            $this->document->links->remove('auth');
        }

        $link = $this->url . 'auth/index?client_id=' . $this->auth->getClientId() . '&state=' . $this->auth->getState();
        if ($this->auth->getSource() !== null){
            $link .= '&source=' . $this->auth->getSource();
        }

        $this->document->links->add(
            new Link(
                name: 'auth',
                href: $link,
            ),
        );

        if ($this->document->links->has('return')){
            $this->document->links->remove('return');
        }

        if ($this->auth->getClientId() !== null) {
            $this->document->links->add(
                new Link(
                    name: 'return',
                    href: $this->getRedirectionLink(Cancel::class),
                ),
            );
        }
    }

    /**
     * @param string $actionClass
     * @return string
     * @throws Exception
     */
    protected function addFormLink(
        string $actionClass,
    ): string
    {
        $path = explode('\\', $actionClass);
        $actionName = array_pop($path);

        return $this->url . 'auth/actions/' . $actionName;
    }

    /**
     * @param string $modelClass
     * @param string $linkName
     * @param string $method
     * @return void
     * @throws Exception
     */
    protected function addFormAction(
        string $modelClass,
        string $linkName='formAction',
        string $method='POST',
    ): void
    {
        $this->document->links->add(
            new Link(
                name: $linkName,
                href:$this->addFormLink(actionClass: $modelClass),
                meta: new Meta([
                    'method' => $method,
                ]),
            ),
        );
    }
}