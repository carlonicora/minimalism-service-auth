<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class CodesTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'codes';

    /** @var array  */
    protected array $fields = [
        'codeId'        => FieldInterface::INTEGER
                        +  FieldInterface::PRIMARY_KEY
                        +  FieldInterface::AUTO_INCREMENT,
        'userId'        => FieldInterface::INTEGER,
        'code'          => FieldInterface::INTEGER,
        'type'          => FieldInterface::STRING,
        'creationTime'  => FieldInterface::STRING
                        +  FieldInterface::TIME_CREATE,
        'expirationTime'=> FieldInterface::STRING
    ];

    /**
     * @throws Exception
     */
    public function purgeExpired() : void
    {
        $this->sql = $this->query->DELETE()
            . ' WHERE expirationTime<?;';
        $this->parameters = ['s', date('Y-m-d H:i:s')];

        $this->functions->runSql();
    }

    /**
     * @param int $userId
     * @throws Exception
     */
    public function purgeUserId(int $userId) : void
    {
        $this->sql = $this->query->DELETE()
            . ' WHERE userId=?;';
        $this->parameters = ['i', $userId];

        $this->functions->runSql();
    }

    /**
     * @param int $userId
     * @param int $code
     * @return array
     * @throws Exception
     * @throws DbRecordNotFoundException
     */
    public function userIdCode(int $userId, int $code): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE userId=? AND code=?;';
        $this->parameters = ['ii', $userId, $code];

        return $this->functions->runReadSingle();
    }
}