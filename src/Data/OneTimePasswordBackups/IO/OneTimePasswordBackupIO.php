<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\OneTimePasswordBackups\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\Auth\Data\OneTimePasswordBackups\Databases\OneTimePasswordsBackupTable;
use CarloNicora\Minimalism\Services\Auth\Data\OneTimePasswordBackups\DataObjects\OneTimePasswordBackup;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;

class OneTimePasswordBackupIO extends AbstractSqlIO
{
    /**
     * @param int $userId
     * @return OneTimePasswordBackup[]
     * @throws MinimalismException
     */
    public function readByUserId(
        int $userId,
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(OneTimePasswordsBackupTable::class)
                ->addParameter(field: OneTimePasswordsBackupTable::userId, value: $userId),
            responseType: OneTimePasswordBackup::class,
            requireObjectsList: true,
        );
    }

    /**
     * @param int $userId
     * @param string $otp
     * @return OneTimePasswordBackup
     * @throws MinimalismException
     */
    public function readByUserIdOtp(
        int $userId,
        string $otp,
    ): OneTimePasswordBackup
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(OneTimePasswordsBackupTable::class)
                ->addParameter(field: OneTimePasswordsBackupTable::userId, value: $userId)
                ->addParameter(field: OneTimePasswordsBackupTable::otp, value: $otp)
                ->addParameter(field: OneTimePasswordsBackupTable::hasBeenUsed, value: false),
            responseType: OneTimePasswordBackup::class,
        );
    }
}