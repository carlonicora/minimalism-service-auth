<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Accounts;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Events\AuthErrorEvents;
use CarloNicora\Minimalism\Services\Auth\Factories\CodeFactory;
use CarloNicora\Minimalism\Services\Encrypter\Encrypter;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class DoAccountLookup extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $email=null;

    /** @var bool|null  */
    protected ?bool $overridePassword=false;

    /** @var bool|null  */
    protected ?bool $recoverPassword=false;
    /** @var bool|null  */
    protected ?bool $create=false;

    /** @var array  */
    protected array $parameters = [
        'email' => [
            ParameterInterface::IS_REQUIRED => true,
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_STRING
        ],
        'create' => [
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_BOOL
        ],
        'overridePassword' => [
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_BOOL
        ],
        'recoverPassword' => [
            ParameterInterface::VALIDATOR => ParameterValidator::PARAMETER_TYPE_BOOL
        ],
    ];

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        if ($this->auth->getUserId() !== null) {
            $user = $this->auth->getAuthenticationTable()->authenticateById($this->auth->getUserId());
        } elseif (($user = $this->auth->getAuthenticationTable()->authenticateByEmail($this->email)) === null) {
            $this->services->logger()->error()->log(
                AuthErrorEvents::INVALID_ACCOUNT()
            )->throw();
        } elseif ($this->create === true) {
            $user = $this->auth->getAuthenticationTable()->generateNewUser($this->email);
        }

        /** @var Encrypter $encrypter */
        $encrypter = $this->services->service(Encrypter::class);

        if ($this->recoverPassword){
            //TODO intialise password recovery

            $this->document->meta->add(
                'recovery',
                true
            );
        } else {
            if ($this->overridePassword || !empty($user['password'])) {
                $codeFactory = new CodeFactory($this->services);

                $codeFactory->generateAndSendCode($user);

                $redirection = 'code/' . $encrypter->encryptId($user['userId']);
            } else {
                $redirection = 'password/' . $encrypter->encryptId($user['userId']);
            }

            $this->document->meta->add(
                'redirection',
                $this->services->paths()->getUrl() . $redirection
            );
        }

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}