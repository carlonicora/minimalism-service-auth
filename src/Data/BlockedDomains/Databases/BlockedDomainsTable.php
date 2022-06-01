<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\BlockedDomains\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'codes', databaseIdentifier: 'Auth')]
enum BlockedDomainsTable
{
    #[SqlField(fieldType: FieldType::Integer,fieldOption: FieldOption::AutoIncrement)]
    case blockedDomainId;

    #[SqlField(fieldType: FieldType::String)]
    case domain;
}