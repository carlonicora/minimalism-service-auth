<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\AppleIds\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'appleIds', databaseIdentifier: 'Auth')]
enum AppleIdsTable
{
    #[SqlField(fieldType: FieldType::String)]
    case appleId;

    #[SqlField(fieldType: FieldType::Integer)]
    case userId;
}