<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Login;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Events\AuthErrorEvents;
use CarloNicora\Minimalism\Services\Auth\Factories\CodeFactory;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class DoCodeLogin extends AbstractAuthWebModel
{
    /** @var int|null  */
    protected ?int $userId;

    /** @var int|null */
    protected ?int $code;

    /** @var array  */
    protected array $parameters = [
        'userId' => [
            ParameterInterface::NAME => 'userId',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::IS_ENCRYPTED => true
        ],
        'code' => [
            ParameterInterface::NAME => 'code',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_INT
        ]
    ];

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        if (($user = $this->auth->getAuthenticationTable()->authenticateById($this->userId)) === null){
            $this->services->logger()->error()->log(
                AuthErrorEvents::INVALID_ACCOUNT()
            )->throw();
        }

        $codeFactory = new CodeFactory($this->services);

        $codeFactory->validateCode($user, $this->code);

        $this->auth->getAuthenticationTable()->activateUser($user);

        $this->auth->setUserId($user['userId']);

        $this->document->links->add(
            new Link('redirection', $this->services->paths()->getUrl() . 'auth')
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}