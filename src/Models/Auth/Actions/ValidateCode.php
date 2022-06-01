<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\Auth\Actions;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthActionModel;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\IO\CodeIO;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Models\Auth\Authorisation;
use Exception;

class ValidateCode extends AbstractAuthActionModel
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
        $user = $this->authenticator->authenticateById($this->auth->getUserId());

        if (!$this->objectFactory->create(CodeIO::class)->isCodeValid(code: $code, userId: $user->getId())){
            throw ExceptionFactory::CodeInvalidOrExpired->create();
        }

        $this->auth->setIsAuthenticated(true);
        $this->objectFactory->create(CodeIO::class)->purgeUserId($user->getId());

        $this->addRedirection(Authorisation::class);

        return HttpCode::Accepted;
    }
}