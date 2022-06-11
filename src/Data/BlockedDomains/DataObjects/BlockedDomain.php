<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\BlockedDomains\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\Auth\Data\BlockedDomains\Databases\BlockedDomainsTable;

#[DbTable(tableClass: BlockedDomainsTable::class)]
class BlockedDomain implements SqlDataObjectInterface
{
    use SqlDataObjectTrait;

    /** @var int  */
    #[DbField(field: BlockedDomainsTable::blockedDomainId)]
    protected int $blockedDomainId;

    /** @var string  */
    #[DbField(field: BlockedDomainsTable::domain)]
    protected string $domain;

    /** @return int */
    public function getBlockedDomainId(): int{return $this->blockedDomainId;}

    /** @param int $blockedDomainId */
    public function setBlockedDomainId(int $blockedDomainId): void{$this->blockedDomainId = $blockedDomainId;}

    /** @return string */
    public function getDomain(): string{return $this->domain;}

    /** @param string $domain */
    public function setDomain(string $domain): void{$this->domain = $domain;}
}