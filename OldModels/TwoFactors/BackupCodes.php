<?php
namespace OldModels\TwoFactors;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\OneTimePasswordsBackupTable;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class BackupCodes extends AbstractAuthWebModel
{
    /** @var string|null  */
    protected ?string $view = 'backupcodes';

    /**
     * @param Auth $auth
     * @param MySQL $mysql
     * @param Path $path
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        Auth $auth,
        MySQL $mysql,
        Path $path,
    ): HttpCode
    {
        /** @var OneTimePasswordsBackupTable $otpbTable */
        $otpbTable = $mysql->create(dbReader: OneTimePasswordsBackupTable::class);

        $backupCodes = $otpbTable->readByUserId(userId: $auth->getUserId());

        if ($backupCodes === []) {
            for ($backupCodeCount = 0; $backupCodeCount < 10; $backupCodeCount++) {
                try {
                    $otp = random_int(100000, 999999);
                } catch (Exception) {
                    /** @noinspection RandomApiMigrationInspection */
                    $otp = rand(100000, 999999);
                }

                $backupCodes[] = [
                    'userId' => $auth->getUserId(),
                    'otp' => (string)$otp,
                    'hasBeenUsed' => false,
                ];
            }

            $otpbTable->update($backupCodes);
        }

        foreach ($backupCodes as $backupCode){
            $this->document->addResource(
                resource: new ResourceObject(
                    type: 'code',
                    id: $backupCode['otp'],
                ),
            );
        }

        $this->document->links->add(
            new Link(
                name: 'auth',
                href: $path->getUrl() . 'auth',
            )
        );

        return HttpCode::Ok;
    }
}