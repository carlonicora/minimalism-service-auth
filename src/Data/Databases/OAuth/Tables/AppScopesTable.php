<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;

class AppScopesTable extends AbstractTable {
    /** @var string */
    protected string $tableName = 'appScopes';

    /** @var array  */
    protected array $fields = [
        'appId'     => TableInterface::INTEGER
                    +  TableInterface::PRIMARY_KEY,
        'scopeId'   => TableInterface::INTEGER
                    +  TableInterface::PRIMARY_KEY
    ];
}