<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;

class AppScopesTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'appScopes';

    /** @var array  */
    protected static array $fields = [
        'appId'     => FieldInterface::INTEGER
                    +  FieldInterface::PRIMARY_KEY,
        'scopeId'   => FieldInterface::INTEGER
                    +  FieldInterface::PRIMARY_KEY
    ];
}