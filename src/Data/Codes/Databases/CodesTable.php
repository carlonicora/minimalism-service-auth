<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Codes\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'codes', databaseIdentifier: 'Auth')]
enum CodesTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer, fieldOption: SqlFieldOption::AutoIncrement)]
    case codeId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case userId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case email;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case code;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeUpdate)]
    case expirationTime;
}