<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ThirdPartyLoginFactory;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class Register extends AbstractAuthWebModel
{
    /** @var string  */
    protected string $viewName = 'register';

    /** @var string|null  */
    protected ?string $clientId=null;

    /** @var string|null  */
    protected ?string $state=null;

    /** @var string|null  */
    protected ?string $errorMessage=null;

    /** @var array|array[]  */
    protected array $parameters = [
        'client_id' => [
            'name' => 'clientId',
            'validator' => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        'state' => [
            'validator' => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        'errorMessage' => [
            'name' => 'errorMessage'
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

        if ($this->auth->getUserId() !== null){
            $this->redirectPage = 'auth';
        }
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $this->document->links->add(
            new Link('doRegister', $this->services->paths()->getUrl() . 'Accounts/Doaccountlookup')
        );

        try {
            $app = $this->auth->getAppByClientId();
            $this->document->links->add(
                new Link('doCancel', $app['url'])
            );
        } catch (Exception $e) {
        }

        if ($this->errorMessage !== null){
            $this->document->meta->add(
                'errorMessage', $this->errorMessage
            );
        }

        $thirdPartyLogins = new ThirdPartyLoginFactory($this->services);
        $thirdPartyLogins->Facebook($this->document);
        $thirdPartyLogins->Google($this->document);
        $thirdPartyLogins->Apple($this->document);

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}