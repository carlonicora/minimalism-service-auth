<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use Exception;

class Facebook extends AbstractAuthWebModel
{
    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}