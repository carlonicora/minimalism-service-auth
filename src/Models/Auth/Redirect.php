<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use Exception;

class Redirect extends AbstractAuthWebModel
{
    /**
     * @return HttpCode
     * @throws Exception
     */
    public function get(
    ): HttpCode
    {
        if ($this->auth->getClientId() === null){
            throw ExceptionFactory::MissingClientId->create();
        }

        if ($this->auth->getUserId() === null){
            throw ExceptionFactory::MissingUserInformation->create();
        }

        /*
        if ($this->auth->getState() === null){
            throw ExceptionFactory::MissingState->create();
        }
        */

        if (!$this->auth->isAuthenticated()){
            return $this->redirect(Cancel::class);
        }

        header('Location: ' . $this->oauth->generateRedirection(
                clientId: $this->auth->getClientId(),
                userId: $this->auth->getUserId(),
                state: $this->auth->getState(),
            ),
        );
        $this->auth->cleanData();

        exit;
    }
}