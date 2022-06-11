<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\OneTimePasswordBackups\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'oneTimePasswordsBackup', databaseIdentifier: 'Auth')]
enum OneTimePasswordsBackupTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer ,fieldOption: SqlFieldOption::AutoIncrement)]
    case oneTimePasswordsBackupId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case userId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case otp;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case hasBeenUsed;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;
}