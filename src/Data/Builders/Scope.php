<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Builders;

use CarloNicora\Minimalism\Interfaces\CacheBuilderFactoryInterface;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\ScopesTable;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts\AbstractResourceBuilder;

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
            ->setIsRequired(true);

        $this->generateAttribute('name');
    }

    /**
     *
     */
    protected function setLinks(): void {}

    /**
     *
     */
    protected function setRelationships(): void {}

    /**
     * @param CacheBuilderFactoryInterface $cacheFactory
     */
    public function setCacheFactoryInterface(CacheBuilderFactoryInterface $cacheFactory): void {}
}