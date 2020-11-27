<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;

class AppScopesTable extends AbstractTable
{
    /** @var string */
    protected string $tableName = 'appScopes';

    /** @var array  */
    protected array $fields = [
        'appId'     => FieldInterface::INTEGER
                    +  FieldInterface::PRIMARY_KEY,
        'scopeId'   => FieldInterface::INTEGER
                    +  FieldInterface::PRIMARY_KEY
    ];
}