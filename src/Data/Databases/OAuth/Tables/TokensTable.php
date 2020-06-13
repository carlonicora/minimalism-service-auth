<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;

class TokensTable extends AbstractTable {
    /** @var string */
    protected string $tableName = 'tokens';

    /** @var array  */
    protected array $fields = [
        'tokenId'   => TableInterface::INTEGER
                    +  TableInterface::PRIMARY_KEY
                    +  TableInterface::AUTO_INCREMENT,
        'appId'     => TableInterface::INTEGER,
        'userId'    => TableInterface::INTEGER,
        'token'     => TableInterface::STRING
    ];
}