<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth;

use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use Exception;

class Cancel extends AbstractAuthWebModel
{
    /**
     * @return never
     * @throws Exception
     */
    public function get(
    ): never
    {
        header('Location: ' . $this->oauth->generateRedirection(
                clientId: $this->auth->getClientId(),
                state: $this->auth->getState(),
            ),
        );

        $this->auth->cleanData();
        exit;
    }
}