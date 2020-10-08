<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Reset;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Events\AuthErrorEvents;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class Dopasswordreset extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $userId;

    /** @var string|null  */
    protected ?string $password;

    /** @var array  */
    protected array $parameters = [
        'userId' => [
            ParameterInterface::NAME => 'userId',
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::IS_ENCRYPTED => true
        ],
        'password' => [
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
        if ($this->auth->getAuthenticationTable()->authenticateById($this->userId) === null) {
            $this->services->logger()->error()->log(
                AuthErrorEvents::INVALID_EMAIL_OR_PASSWORD()
            )->throw();
        }

        $this->auth->getAuthenticationTable()->updatePassword($this->userId, $this->password);

        $this->auth->setUserId($this->userId);

        $this->document->meta->add(
            'redirection',
            $this->services->paths()->getUrl() . 'auth'
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}