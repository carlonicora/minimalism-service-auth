<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\AppleIds\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'appleIds', databaseIdentifier: 'Users')]
enum AppleIdsTable
{
    #[SqlField(fieldType: FieldType::String,fieldOption: FieldOption::PrimaryKey)]
    case appleId;

    #[SqlField(fieldType: FieldType::Integer)]
    case userId;
}