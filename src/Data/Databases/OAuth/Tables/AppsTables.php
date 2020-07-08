<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;

class AppsTables extends AbstractTable
{
    /** @var string */
    protected string $tableName = 'apps';

    /** @var array  */
    protected array $fields = [
        'appId'         => TableInterface::INTEGER
            +  TableInterface::PRIMARY_KEY
            +  TableInterface::AUTO_INCREMENT,
        'userId'        => TableInterface::INTEGER,
        'name'          => TableInterface::STRING,
        'url'           => TableInterface::STRING,
        'isActive'      => TableInterface::INTEGER,
        'isTrusted'     => TableInterface::INTEGER,
        'clientId'      => TableInterface::STRING,
        'clientSecret'  => TableInterface::STRING,
        'creationTime'  => TableInterface::STRING
                        +  TableInterface::TIME_CREATE
    ];

    /**
     * @param string $clientId
     * @return array
     * @throws DbRecordNotFoundException
     * @throws DbSqlException
     */
    public function getByClientId(string $clientId): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE clientId=?;';
        $this->parameters = ['s', $clientId];

        return $this->functions->runReadSingle();
    }
}