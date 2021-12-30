<?php
namespace CarloNicora\Minimalism\Services\Auth\Models\TwoFactors;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\OneTimePasswordsBackupTable;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use RuntimeException;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

class DoValidation extends AbstractAuthWebModel
{
    /**
     * @param Auth $auth
     * @param Path $path
     * @param MySQL $mysql
     * @param string $code
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        Auth $auth,
        Path $path,
        MySQL $mysql,
        string $code,
    ): HttpCode
    {
        $authenticator = new GoogleAuthenticator();
        $tolerance = 1;

        $salt = $auth->getAuthenticationTable()->authenticateById($auth->getUserId())?->getSalt();

        if (!$authenticator->checkCode($salt, $code, $tolerance)){
            /** @var OneTimePasswordsBackupTable $otpbTable */
            $otpbTable = $mysql->create(dbReader: OneTimePasswordsBackupTable::class);
            
            $backupCode = $otpbTable->readByUserIdOtp(
                userId: $auth->getUserId(),
                otp: $code,
            );

            if ($backupCode === []) {
                throw new RuntimeException('Code does not match', HttpCode::Unauthorized->value);
            }

            $backupCode[0]['hasBeenUsed'] = true;
            $otpbTable->update($backupCode);
        }

        $auth->set2faValiationConfirmed();

        $this->document->meta->add(
            'redirection',
            $path->getUrl() . 'auth'
        );

        return HttpCode::Ok;
    }
}