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

    /** @var int */
    #[DbField(field: CodesTable::codeId)]
    private int $id;

    /** @var int|null */
    #[DbField]
    private ?int $userId=null;

    /** @var string|null */
    #[DbField]
    private ?string $email=null;

    /** @var int */
    #[DbField]
    private int $code;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $expirationTime;

    /** @return int */
    public function getId(): int{return $this->id;}

    /** @param int $id */
    public function setId(int $id): void{$this->id = $id;}

    /** @return int|null */
    public function getUserId(): ?int{return $this->userId;}

    /** @param int|null $userId */
    public function setUserId(?int $userId): void{$this->userId = $userId;}

    /** @return string|null */
    public function getEmail(): ?string{return $this->email;}

    /** @param string|null $email */
    public function setEmail(?string $email): void{$this->email = $email;}

    /** @return int|null */
    public function getCode(): ?int{return $this->code;}

    /** @param int|null $code */
    public function setCode(?int $code): void{$this->code = $code;}

    /** @return int */
    public function getExpirationTime(): int{return $this->expirationTime;}

    /** @param int $expirationTime */
    public function setExpirationTime(int $expirationTime): void{$this->expirationTime = $expirationTime;}

    /** @return int */
    public function getCreatedAt(): int{return $this->createdAt;}

    /** @param int $createdAt */
    public function setCreatedAt(int $createdAt): void{$this->createdAt = $createdAt;}
}