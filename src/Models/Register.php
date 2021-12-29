<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth as AuthService;
use CarloNicora\Minimalism\Services\Auth\Factories\ThirdPartyLoginFactory;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Register extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'register';

    /**
     * @param AuthService $auth
     * @param Path $path
     * @param string|null $client_id
     * @param string|null $state
     * @param string|null $errorMessage
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        AuthService $auth,
        Path $path,
        ?string $client_id,
        ?string $state,
        ?string $errorMessage,
    ): HttpCode
    {
        if ($client_id !== null) {
            $auth->setClientId($client_id);
        }

        if ($state !== null) {
            $auth->setState($state);
        }

        if ($auth->getUserId() !== null){
            return $this->redirect(
                modelClass: Auth::class,
            );
            //$this->redirectionParameters = [];
        }

        $this->document->links->add(
            new Link('doRegister', $path->getUrl() . 'Accounts/Doaccountlookup')
        );

        try {
            $app = $auth->getAppByClientId();

            if ($app !== []) {
                $this->document->links->add(
                    new Link('doCancel', $app[0]['url'])
                );
            }
        } catch (Exception) {
        }

        if ($errorMessage !== null){
            $this->document->meta->add(
                'errorMessage', $errorMessage
            );
        }

        $thirdPartyLogins = new ThirdPartyLoginFactory(
            auth: $auth,
            path: $path,
        );
        $thirdPartyLogins->Facebook($this->document);
        $thirdPartyLogins->Google($this->document);
        $thirdPartyLogins->Apple($this->document);

        return HttpCode::Ok;
    }
}