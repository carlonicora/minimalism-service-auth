<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use Exception;

class Login extends AbstractAuthWebModel
{
    /** @var string  */
    protected string $viewName = 'login';

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $this->document->links->add(
            new Link('doLogin', $this->services->paths()->getUrl() . 'dologin')
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}