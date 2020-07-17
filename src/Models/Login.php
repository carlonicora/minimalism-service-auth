<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class Login extends AbstractAuthWebModel
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
            new Link('doLogin', $this->services->paths()->getUrl() . 'Accounts/DoAccountLookup')
        );

        $this->document->links->add(
            new Link('registration', $this->services->paths()->getUrl() . 'register')
        );

        $this->document->links->add(
            new Link('forgot', $this->services->paths()->getUrl() . 'forgot')
        );

        $this->addFacebookLogin();
        $this->addGoogleLogin();

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }

    private function addGoogleLogin(): void
    {
        /*
        try {
            $client = new Google_Client();
            $client->setAuthConfig($this->configurations->googleFile);
            $client->setRedirectUri($this->configurations->getBaseUrl() . '/google');
            $client->addScope('email');
            $client->addScope('profile');
            $authUrl = $client->createAuthUrl();

            $this->response->addLink('google', $authUrl);
        } catch (Exception $error ){
            errorReporter::report($this->configurations, '', 'Google splash failed to be initialised. Aborting Google Login: ' . $error->getMessage());
        }
        */
    }

    private function addFacebookLogin(): void
    {
        /*
        try {
            $fb = new Facebook([
                'app_id' => $this->configurations->facebookId,
                'app_secret' => $this->configurations->facebookSecret,
                'default_graph_version' => 'v5.0',
            ]);
            $helper = $fb->getRedirectLoginHelper();
            $permissions = ['email'];
            $loginUrl = $helper->getLoginUrl($this->configurations->getBaseUrl() . 'facebook', $permissions);

            $this->response->addLink('facebook', $loginUrl);
        } catch (Exception $error){
            errorReporter::report($this->configurations, '', 'Facebook splash failed to be initialised. Aborting Facebook Login: ' . $error->getMessage());
        }
        */
    }
}