<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\IO\CodeIO;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Username;
use Exception;

class ValidateActivation extends AbstractAuthActionModel
{
    /**
     * @param int $code
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        int $code,
    ): HttpCode
    {
        if ($this->auth->getEmail() === null){
            throw ExceptionFactory::EmailNotFound->create();
        }

        if (!$this->objectFactory->create(CodeIO::class)->isCodeValid(code: $code, email: $this->auth->getEmail())){
            throw ExceptionFactory::CodeInvalidOrExpired->create();
        }

        $this->objectFactory->create(CodeIO::class)->purgeEmail($this->auth->getEmail());

        $user = $this->authenticator->generateNewUser(
            email: $this->auth->getEmail()
        );
        $this->authenticator->activateUser($user);
        $this->auth->setIsAuthenticated(true);
        $this->auth->setIsNewRegistration();
        $this->auth->setUserId($user->getId());
        $this->auth->setEmail(null);

        $this->addRedirection(Username::class);

        return HttpCode::Accepted;
    }
}