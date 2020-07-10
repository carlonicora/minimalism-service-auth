<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;

class CodesTable extends AbstractTable
{
    /** @var string */
    protected string $tableName = 'codes';

    /** @var array  */
    protected array $fields = [
        'codeId'        => TableInterface::INTEGER
                        +  TableInterface::PRIMARY_KEY
                        +  TableInterface::AUTO_INCREMENT,
        'userId'        => TableInterface::INTEGER,
        'code'          => TableInterface::INTEGER,
        'type'          => TableInterface::STRING,
        'creationTime'  => TableInterface::STRING
                        +  TableInterface::TIME_CREATE,
        'expirationTime'=> TableInterface::STRING
    ];

    /**
     * @throws DbSqlException
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
     * @throws DbSqlException
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
     * @throws DbSqlException
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