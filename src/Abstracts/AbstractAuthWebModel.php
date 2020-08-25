<?php
namespace CarloNicora\Minimalism\Services\Auth\Abstracts;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Modules\Web\WebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class AbstractAuthWebModel extends WebModel
{
    /** @var Auth  */
    protected Auth $auth;

    /** @var MySQL  */
    protected MySQL $mysql;

    /** @var JsonDataMapper  */
    protected JsonDataMapper $mapper;

    /**
     * AbstractAuthWebModel constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        parent::__construct($services);
        $this->auth = $services->service(Auth::class);
        $this->mysql = $services->service(MySQL::class);
        $this->mapper = $services->service(JsonDataMapper::class);
    }

    /**
     * @param array $passedParameters
     * @param array|null $file
     * @throws Exception
     */
    public function initialise(array $passedParameters, array $file = null): void
    {
        parent::initialise($passedParameters, $file);

        $this->document->links->add(
            new Link('home', $this->services->paths()->getUrl())
        );
    }
}