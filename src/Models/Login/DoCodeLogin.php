<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Login;

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
    protected ?int $userIdForm=null;

    /** @var int|null */
    protected ?int $codeForm=null;

    /** @var int|null  */
    protected ?int $userIdLink=null;

    /** @var int|null */
    protected ?int $codeLink=null;

    /** @var string|null  */
    protected ?string $clientId=null;

    /** @var string|null */
    protected ?string $state=null;

    /** @var array  */
    protected array $parameters = [
        0 => [
            ParameterInterface::NAME => 'userIdLink',
            ParameterInterface::IS_ENCRYPTED => true
        ],
        1 => [
            ParameterInterface::NAME => 'codeLink',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_INT
        ],
        2 => [
            ParameterInterface::NAME => 'clientId',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        3 => [
            ParameterInterface::NAME => 'state',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        'userId' => [
            ParameterInterface::NAME => 'userIdForm',
            ParameterInterface::IS_ENCRYPTED => true
        ],
        'code' => [
            ParameterInterface::NAME => 'codeForm',
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_INT
        ]
    ];

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $code = $this->codeForm ?? $this->codeLink;
        $userId = $this->userIdForm ?? $this->userIdLink;

        if (($user = $this->auth->getAuthenticationTable()->authenticateById($userId)) === null){
            $this->services->logger()->error()->log(
                AuthErrorEvents::INVALID_ACCOUNT()
            )->throw();
        }

        $codeFactory = new CodeFactory($this->services);

        $codeFactory->validateCode($user, $code);

        if (!$user['isActive']) {
            $this->auth->getAuthenticationTable()->activateUser($user);
        }

        $this->auth->setUserId($user['userId']);

        if ($this->userIdForm !== null) {
            $this->document->meta->add(
                'redirection',
                $this->services->paths()->getUrl() . 'auth'
            );
        } else {
            header(
                'location: '
                . $this->services->paths()->getUrl()
                . 'auth?client_id=' . $this->clientId . '&state=' . $this->state);
        }

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}