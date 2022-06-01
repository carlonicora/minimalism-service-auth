<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\IO\CodeIO;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Authorisation;
use Exception;

class CompleteReset extends AbstractAuthActionModel
{
    /**
     * @param int $code
     * @param string $password
     * @return HttpCode
     * @throws Exception
     */
    public function patch(
        int $code,
        string $password,
    ): HttpCode
    {
        $user = $this->authenticator->authenticateById($this->auth->getUserId());

        if (!$this->objectFactory->create(CodeIO::class)->isCodeValid(code: $code, userId: $user->getId())){
            throw ExceptionFactory::CodeInvalidOrExpired->create();
        }

        $this->authenticator->updatePassword($user->getId(), password_hash($password, PASSWORD_BCRYPT));

        $this->objectFactory->create(CodeIO::class)->purgeUserId($user->getId());
        $this->auth->setUserId($user->getId());
        $this->auth->setIsAuthenticated(true);

        $this->addRedirection(Authorisation::class);

        return HttpCode::Accepted;
    }
}