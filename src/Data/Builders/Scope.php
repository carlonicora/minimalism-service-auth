<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Builders;

use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\ScopesTable;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;

class Scope extends AbstractResourceBuilder
{
    /** @var string  */
    public string $type = 'scope';

    /** @var string|null  */
    public ?string $tableName = ScopesTable::class;

    /**
     *
     */
    protected function setAttributes(): void
    {
        $this->generateAttribute('id')
            ->setDatabaseFieldName('scopeId')
            ->setIsEncrypted(true)
            ->setIsRequired(true)
            ->setType(ParameterValidator::PARAMETER_TYPE_INT);

        $this->generateAttribute('name')
            ->setType(ParameterValidator::PARAMETER_TYPE_STRING);
    }

    /**
     *
     */
    protected function setLinks(): void
    {
    }

    /**
     *
     */
    protected function setRelationships(): void
    {
    }
}