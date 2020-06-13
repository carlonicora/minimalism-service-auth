<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use Exception;
use RuntimeException;

class Authorise extends AbstractAuthWebModel
{
    /**
     * @return ResponseInterface
     * @throws DbRecordNotFoundException|DbSqlException|Exception
     */
    public function generateData(): ResponseInterface
    {
        if ($this->auth->getClientId() === null) {
            throw new RuntimeException('client_id missing', 412);
        }

        $app = $this->auth->getAppByClientId();

        if (!$app['isActive']) {
            throw new RuntimeException('application is not active', 412);
        }

        $auth = $this->auth->generateAuth($app['appId']);
        $redirection = $this->auth->generateRedirection($app, $auth);

        $this->document->meta->add('redirection', $redirection);

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}