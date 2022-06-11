<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\BlockedDomains\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'codes', databaseIdentifier: 'Auth')]
enum BlockedDomainsTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer,fieldOption: SqlFieldOption::AutoIncrement)]
    case blockedDomainId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case domain;
}