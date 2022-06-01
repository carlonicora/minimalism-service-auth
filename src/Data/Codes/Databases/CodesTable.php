<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Codes\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'codes', databaseIdentifier: 'Auth')]
enum CodesTable
{
    #[SqlField(fieldType: FieldType::Integer, fieldOption: FieldOption::AutoIncrement)]
    case codeId;

    #[SqlField(fieldType: FieldType::Integer)]
    case userId;

    #[SqlField]
    case email;

    #[SqlField(fieldType: FieldType::Integer)]
    case code;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case expirationTime;
}