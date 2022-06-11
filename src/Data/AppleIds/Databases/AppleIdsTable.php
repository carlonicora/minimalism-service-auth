<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\AppleIds\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'appleIds', databaseIdentifier: 'Auth')]
enum AppleIdsTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case appleId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case userId;
}