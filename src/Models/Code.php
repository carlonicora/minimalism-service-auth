<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class Code extends AbstractAuthWebModel
{
    /** @var int|null  */
    protected ?int $userId;

    /** @var bool  */
    protected bool $create=false;

    /** @var array  */
    protected array $parameters = [
        0 => [
            ParameterInterface::NAME => 'userId',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::IS_ENCRYPTED => true
        ],
        1 => [
            ParameterInterface::NAME => 'create',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_BOOL
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
        $user = $this->auth->getAuthenticationTable()->authenticateById($this->userId);

        $userResource = new ResourceObject('user', $this->encrypter->encryptId($this->userId));
        $userResource->attributes->add('email', $user['email']);
        $userResource->attributes->add('new', $this->create);

        $this->document->addResource($userResource);

        $this->document->links->add(
            new Link('doLogin', $this->services->paths()->getUrl() . 'Login/DoCodeLogin')
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}