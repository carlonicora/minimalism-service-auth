<?php
namespace CarloNicora\Minimalism\Services\Auth\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Factories\MinimalismFactories;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Interfaces\AuthenticationInterface;
use Exception;

class AbstractAuthModel extends AbstractModel
{
    /** @var AuthenticationInterface  */
    protected AuthenticationInterface $authenticator;

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

        $this->url = $minimalismFactories->getServiceFactory()->getPath()?->getUrl();
        $this->auth = $minimalismFactories->getServiceFactory()->create(Auth::class);
        $this->authenticator = $this->auth->getAuthenticationTable();
    }

    /**
     * @param string $pageClass
     * @param array|null $positionedParameters
     * @param array $parameters
     * @return string
     */
    protected function getRedirectionLink(
        string $pageClass,
        ?array $positionedParameters=null,
        array  $parameters = [],
    ): string
    {
        $path = explode('\\', $pageClass);
        $pageName = array_pop($path);

        $response = $this->url . 'auth/' . $pageName . '/';

        if ($positionedParameters !== null){
            $response .= implode('/', $positionedParameters);
        }

        $isFirstParameter = true;
        foreach ($parameters ?? [] as $parameterName => $parameterValue){
            $response .= ($isFirstParameter ? '?' : '&') . $parameterName . '=' . $parameterValue;
            $isFirstParameter = false;
        }

        return $response;
    }
}