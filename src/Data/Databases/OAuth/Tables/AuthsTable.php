<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;

class AuthsTable extends AbstractTable {
    /** @var string */
    protected string $tableName = 'auths';

    /** @var array  */
    protected array $fields = [
        'authId'        => TableInterface::INTEGER
                        +  TableInterface::PRIMARY_KEY
                        +  TableInterface::AUTO_INCREMENT,
        'appId'         => TableInterface::INTEGER,
        'userId'        => TableInterface::INTEGER,
        'expiration'    => TableInterface::STRING,
        'code'          => TableInterface::STRING
    ];

    /**
     * @param string $code
     * @return array
     * @throws DbSqlException|DbRecordNotFoundException
     */
    public function loadByCode(string $code): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE code=?;';
        $this->parameters = ['s', $code];

        return $this->functions->runReadSingle();
    }
}