<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use Exception;

class Code extends AbstractAuthWebModel
{
    /** @var int|null  */
    protected ?int $userId;

    /** @var array  */
    protected array $parameters = [
        'id' => [
            ParameterInterface::NAME => 'userId',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::IS_ENCRYPTED => true
        ]
    ];

    /** @var string  */
    protected string $viewName = 'code';

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $this->document->addResource(new ResourceObject('user', $this->userId));

        $this->document->links->add(
            new Link('doLogin', $this->services->paths()->getUrl() . 'Login/DoCodeLogin')
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}