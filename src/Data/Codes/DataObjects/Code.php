<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Codes\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\Databases\CodesTable;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;

#[DbTable(tableClass: CodesTable::class)]
class Code implements SqlDataObjectInterface
{
    use SqlDataObjectTrait;

    /** @var int  */
    #[DbField]
    private int $codeId;

    /** @var int  */
    #[DbField]
    private int $userId;

    /** @var int  */
    #[DbField]
    private int $code;

    /** @var string|null  */
    #[DbField]
    private ?string $type=null;

    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $creationTime;

    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $expirationTime;

    /** @return int */
    public function getCodeId(): int{return $this->codeId;}

    /** @param int $codeId */
    public function setCodeId(int $codeId): void{$this->codeId = $codeId;}

    /** @return int */
    public function getUserId(): int{return $this->userId;}

    /** @param int $userId */
    public function setUserId(int $userId): void{$this->userId = $userId;}

    /** @return int */
    public function getCode(): int{return $this->code;}

    /** @param int $code */
    public function setCode(int $code): void{$this->code = $code;}

    /** @return string|null */
    public function getType(): ?string{return $this->type;}

    /** @param string|null $type */
    public function setType(?string $type): void{$this->type = $type;}

    /** @return int */
    public function getCreationTime(): int{return $this->creationTime;}

    /** @return int */
    public function getExpirationTime(): int{return $this->expirationTime;}

    /** @param int $expirationTime */
    public function setExpirationTime(int $expirationTime): void{$this->expirationTime = $expirationTime;}
}