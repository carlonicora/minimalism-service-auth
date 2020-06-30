<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Data\Builders\App;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;
use RuntimeException;

class Auth extends AbstractAuthWebModel
{
    /** @var string  */
    protected string $viewName = 'auth';

    /** @var string|null  */
    protected ?string $clientId=null;

    /** @var string|null  */
    protected ?string $state=null;

    /** @var array|array[]  */
    protected array $parameters = [
        'client_id' => [
            'name' => 'clientId',
            'validator' => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        'state' => [
            'validator' => ParameterValidator::PARAMETER_TYPE_STRING
        ]
    ];

    /**
     * @param array $passedParameters
     * @param array|null $file
     * @throws Exception
     */
    public function initialise(array $passedParameters, array $file = null): void
    {
        parent::initialise($passedParameters, $file);

        if ($this->clientId !== null) {
            $this->auth->setClientId($this->clientId);
        }

        if ($this->state !== null) {
            $this->auth->setState($this->state);
        }

        if ($this->auth->getClientId() === null) {
            throw new RuntimeException('client_id missing', 412);
        }

        if ($this->auth->getUserId() === null){
            $this->redirectPage = 'Login';
        }
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $app = $this->auth->getAppByClientId();

        if (!$app['isActive']) {
            throw new RuntimeException('application is not active', 412);
        }

        if ($app['isTrusted']) {
            $auth = $this->auth->generateAuth($app['appId']);
            $redirection = $this->auth->generateRedirection($app, $auth);

            header('Location: ' . $redirection);
            exit;
        }

        $this->document->links->add(
            new Link('authorise', $this->services->paths()->getUrl() . 'Authorisation/DoAuthorise')
        );

        $this->document->addResourceList(
            $this->mapper->generateResourceObjectByFieldValue(
                App::class,
                null,
                App::attributeId(),
                $app['appId'],
                true
            )
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}