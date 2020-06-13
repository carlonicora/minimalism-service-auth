<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Login;

use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Events\AuthErrorEvents;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class Login extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $email;

    /** @var string|null  */
    protected ?string $password;

    /** @var array  */
    protected array $parameters = [
        'email' => ['required' => true, 'validator' => ParameterValidator::PARAMETER_TYPE_STRING],
        'password' => ['required' => true, 'validator' => ParameterValidator::PARAMETER_TYPE_STRING]
    ];

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function generateData(): ResponseInterface
    {
        if (($user = $this->auth->getAuthenticationTable()->authenticateByEmail($this->email)) === null){
            $this->services->logger()->error()->log(
                AuthErrorEvents::INVALID_EMAIL_OR_PASSWORD()
            )->throw();
        }

        if (!$this->decryptPassword($this->password, $user['password'])){
            $this->services->logger()->error()->log(
                AuthErrorEvents::INVALID_EMAIL_OR_PASSWORD()
            )->throw();
        }

        if (array_key_exists('activationDate', $user) && empty($user['activationDate'])) {
            $this->services->logger()->error()->log(
                AuthErrorEvents::ACCOUNT_NOT_ACTIVE($user['userId'])
            )->throw(Exception::class, 'The account has not been activated yet');
        }

        $this->auth->setUserId($user['userId']);

        $this->document->meta->add(
            'redirection',
            $this->services->paths()->getUrl() . '/auth'
        );

        return $this->generateResponse($this->document, ResponseInterface::HTTP_STATUS_200);
    }

    /**
     * Verifies if a password matches its hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    private function decryptPassword($password, $hash): bool {
        $returnValue = false;

        if (password_verify($password, $hash)){
            $returnValue = true;
        }

        return $returnValue;
    }
}