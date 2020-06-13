<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
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
}