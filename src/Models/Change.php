<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class Change extends AbstractAuthWebModel
{
    /** @var string  */
    protected string $viewName = 'reset';

    /** @var string|null  */
    protected ?string $userId=null;

    /** @var string|null  */
    protected ?string $clientId=null;

    /** @var string|null  */
    protected ?string $state=null;

    /** @var array|array[]  */
    protected array $parameters = [
        0 => [
            ParameterInterface::NAME => 'userId',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::IS_ENCRYPTED => true
        ],
        1 => [
            ParameterInterface::NAME => 'clientId',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        2 => [
            ParameterInterface::NAME => 'state',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ]
    ];

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $this->document->addResource(new ResourceObject('user', $this->encrypter->encryptId($this->userId)));

        $this->document->links->add(
            new Link('doReset', $this->services->paths()->getUrl() . 'Reset/DoPasswordReset')
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}