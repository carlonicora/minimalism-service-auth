<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Factories\ThirdPartyLoginFactory;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class Register extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'register';

    /**
     * @param \CarloNicora\Minimalism\Services\Auth\Auth $auth
     * @param Path $path
     * @param string|null $client_id
     * @param string|null $state
     * @param string|null $errorMessage
     * @return int
     * @throws Exception
     */
    public function get(
        \CarloNicora\Minimalism\Services\Auth\Auth $auth,
        Path $path,
        ?string $client_id,
        ?string $state,
        ?string $errorMessage,
    ): int
    {
        if ($client_id !== null) {
            $auth->setClientId($client_id);
        }

        if ($state !== null) {
            $auth->setState($state);
        }

        if ($auth->getUserId() !== null){
            $this->redirection = Auth::class;
            $this->redirectionParameters = [];
            return 302;
        }

        $this->document->links->add(
            new Link('doRegister', $path->getUrl() . 'Accounts/Doaccountlookup')
        );

        try {
            $app = $auth->getAppByClientId();
            $this->document->links->add(
                new Link('doCancel', $app['url'])
            );
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

        return 200;
    }
}