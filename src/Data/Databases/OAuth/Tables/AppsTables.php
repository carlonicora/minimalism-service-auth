<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class AppsTables extends AbstractMySqlTable
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
     * @param string $client_id
     * @return array
     * @throws Exception
     */
    public function getByClientId(string $client_id): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE clientId=?;';
        $this->parameters = ['s', $client_id];

        return $this->functions->runRead();
    }
}