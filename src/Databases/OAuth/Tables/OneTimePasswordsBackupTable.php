<?php
namespace CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class OneTimePasswordsBackupTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'oneTimePasswordsBackup';

    /** @var array  */
    protected static array $fields = [
        'oneTimePasswordsBackupId'  => FieldInterface::INTEGER
                                    +  FieldInterface::PRIMARY_KEY
                                    +  FieldInterface::AUTO_INCREMENT,
        'userId'                    => FieldInterface::STRING,
        'otp'                       => FieldInterface::STRING,
        'hasBeenUsed'               => FieldInterface::INTEGER,
    ];

    /**
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function readByUserId(
        int $userId,
    ): array
    {
        $this->sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE userId=?;';
        $this->parameters = ['i', $userId];

        return $this->functions->runRead();
    }

    /**
     * @param int $userId
     * @param string $otp
     * @return array
     * @throws Exception
     */
    public function readByUserIdOtp(
        int $userId,
        string $otp,
    ): array
    {
        $this->sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE userId=? AND otp=? AND hasBeenUsed=?;';
        $this->parameters = ['isi', $userId, $otp, 0];

        return $this->functions->runRead();
    }
}