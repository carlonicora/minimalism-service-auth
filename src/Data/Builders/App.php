<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Builders;

use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\AppScopesTable;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\AppsTables;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class App extends AbstractResourceBuilder
{
    /** @var string  */
    public string $type = 'app';

    /** @var string|null  */
    public ?string $tableName = AppsTables::class;

    /**
     *
     */
    protected function setAttributes(): void
    {
        $this->generateAttribute('id')
            ->setDatabaseFieldName('appId')
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
     * @throws Exception
     */
    protected function setRelationships(): void
    {
        $this->addRelationship(
            $this->relationshipBuilderInterfaceFactory->create(
                RelationshipTypeInterface::MANY_TO_MANY,
                'scopes'
            )->withBuilder(
                Scope::attributeId(),
                'appId'
            )->throughManyToManyTable(
                AppScopesTable::class,
                'scopeId'
            )
        );
    }
}