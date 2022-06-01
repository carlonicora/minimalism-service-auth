<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\BlockedDomains\IO;

use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\Auth\Data\BlockedDomains\Databases\BlockedDomainsTable;
use CarloNicora\Minimalism\Services\Auth\Data\BlockedDomains\DataObjects\BlockedDomain;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use Exception;

class BlockedDomainIO extends AbstractSqlIO
{
    /**
     * @param string $domain
     * @return BlockedDomain
     * @throws Exception
     */
    public function readBydomain(
        string $domain,
    ): BlockedDomain
    {
        $factory = SqlQueryFactory::create(BlockedDomainsTable::class)
            ->addParameter(field: BlockedDomainsTable::domain, value: strtolower($domain));

        return $this->data->read(
            queryFactory: $factory,
            responseType: BlockedDomain::class,
        );
    }
}