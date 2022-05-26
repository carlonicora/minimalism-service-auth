<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\OneTimePasswordBackups\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'oneTimePasswordsBackup', databaseIdentifier: 'Auth')]
enum OneTimePasswordsBackupTable
{
    #[SqlField(fieldType: FieldType::Integer ,fieldOption: FieldOption::AutoIncrement)]
    case oneTimePasswordsBackupId;

    #[SqlField(fieldType: FieldType::Integer)]
    case userId;

    #[SqlField()]
    case otp;

    #[SqlField(fieldType: FieldType::Integer)]
    case hasBeenUsed;
}