<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use Exception;

class Password extends AbstractAuthWebModel
{
    /** @var string  */
    protected string $viewName = 'password';

    /** @var int|null  */
    protected ?int $userId=null;

    /** @var array|array[]  */
    protected array $parameters = [
        0 => [
            ParameterInterface::NAME => 'userId',
            ParameterInterface::IS_ENCRYPTED => true,
            ParameterInterface::IS_REQUIRED => true
        ]
    ];

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $this->document->meta->add('userId', $this->encrypter->encryptId($this->userId));

        $this->document->links->add(
            new Link('doLogin', $this->services->paths()->getUrl() . 'Login/DoPasswordLogin')
        );

        $this->document->links->add(
            new Link('doCodeLogin', $this->services->paths()->getUrl() . 'Accounts/DoAccountLookup/' . $this->encrypter->encryptId($this->userId) . '?overridePassword=true')
        );

        $this->document->links->add(
            new Link('forgot', $this->services->paths()->getUrl() . 'forgot')
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}