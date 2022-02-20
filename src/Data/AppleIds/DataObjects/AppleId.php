<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\AppleIds\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\Databases\AppleIdsTable;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;

#[DbTable(tableClass: AppleIdsTable::class)]
class AppleId implements SqlDataObjectInterface
{
    use SqlDataObjectTrait;

    /** @var string  */
    #[DbField]
    private string $appleId;

    /** @var int  */
    #[DbField]
    private int $userId;

    /** @return string */
    public function getAppleId(): string{return $this->appleId;}

    /** @param string $appleId */
    public function setAppleId(string $appleId): void{$this->appleId = $appleId;}

    /** @return int */
    public function getUserId(): int{return $this->userId;}

    /** @param int $userId */
    public function setUserId(int $userId): void{$this->userId = $userId;}
}