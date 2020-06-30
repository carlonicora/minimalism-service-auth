<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class Change extends AbstractAuthWebModel
{
    /** @var string  */
    protected string $viewName = 'login';

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
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        $this->document->links->add(
            new Link('doLogin', $this->services->paths()->getUrl() . 'Accounts/DoAccountLookup')
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}