<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;

class AppsTables extends AbstractTable
{
    /** @var string */
    protected string $tableName = 'apps';

    /** @var array  */
    protected array $fields = [
        'appId'         => FieldInterface::INTEGER
                        +  FieldInterface::PRIMARY_KEY
                        +  FieldInterface::AUTO_INCREMENT,
        'userId'        => FieldInterface::INTEGER,
        'name'          => FieldInterface::STRING,
        'url'           => FieldInterface::STRING,
        'isActive'      => FieldInterface::INTEGER,
        'isTrusted'     => FieldInterface::INTEGER,
        'clientId'      => FieldInterface::STRING,
        'clientSecret'  => FieldInterface::STRING,
        'creationTime'  => FieldInterface::STRING
                        +  FieldInterface::TIME_CREATE
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