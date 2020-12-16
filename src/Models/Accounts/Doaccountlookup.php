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

class Doaccountlookup extends AbstractAuthWebModel
{
    /** @var int|null  */
    protected ?int $userId=null;

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
        0 => [
            ParameterInterface::NAME => 'userId',
            ParameterInterface::IS_ENCRYPTED => true
        ],
        'email' => [
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
        $codeFactory = new CodeFactory($this->services);

        $user = [];

        if ($this->auth->getUserId() !== null) {
            $user = $this->auth->getAuthenticationTable()->authenticateById($this->auth->getUserId());
        } elseif ($this->create === false && $this->email !== null &&  ($user = $this->auth->getAuthenticationTable()->authenticateByEmail($this->email)) === null) {
            $this->services->logger()->error()->log(
                AuthErrorEvents::INVALID_ACCOUNT()
            )->throw();
        } elseif ($this->userId !== null &&  ($user = $this->auth->getAuthenticationTable()->authenticateById($this->userId)) === null) {
            $this->services->logger()->error()->log(
                autherrorevents::invalid_account()
            )->throw();
        } elseif ($this->create === true && ($user = $this->auth->getAuthenticationTable()->authenticateByEmail($this->email)) === null) {
            $user = $this->auth->getAuthenticationTable()->generateNewUser($this->email);
            $this->auth->setIsNewRegistration();
        }

        /** @var Encrypter $encrypter */
        $encrypter = $this->services->service(Encrypter::class);

        if ($this->recoverPassword){
            $codeFactory->generateAndSendResetCode($user);

            $this->document->meta->add(
                'message',
                'If your email is in our database, we have sent you a message to reset your password'
            );
        } else {
            $redirection = null;

            if ($this->overridePassword || empty($user['password'])) {
                $codeFactory->generateAndSendCode($user);

                if ($this->overridePassword){
                    header(
                        'location:'
                        . $this->services->paths()->getUrl()
                        . 'code/' . $encrypter->encryptId($user['userId'])
                    );
                } elseif ($this->create){
                    $redirection = 'code/' . $encrypter->encryptId($user['userId']) . '/1';
                } else {
                    $redirection = 'code/' . $encrypter->encryptId($user['userId']);
                }
            } else {
                $redirection = 'password/' . $encrypter->encryptId($user['userId']);
            }

            if ($redirection !== null) {
                $this->document->meta->add(
                    'redirection',
                    $this->services->paths()->getUrl() . $redirection
                );
            }
        }

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }
}