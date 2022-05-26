<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Codes\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\OAuth\Data\Codes\Databases\CodesTable;

#[DbTable(tableClass: CodesTable::class)]
class Code implements SqlDataObjectInterface
{
    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: CodesTable::codeId)]
    private int $id;

    /** @var int */
    #[DbField]
    private int $userId;

    /** @var int */
    #[DbField]
    private int $code;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $expirationTime;

    /** @var string|null */
    #[DbField]
    private ?string $type = null;

    /**
     * @param int $userId
     * @param int $code
     */
    public function __construct(
        int $userId,
        int $code,
    )
    {
        $this->userId = $userId;
        $this->code = $code;
        $this->expirationTime = time() + 60 * 5;
    }

    /** @return int */
    public function getId(): int{return $this->id;}

    /** @param int $id */
    public function setId(int $id): void{$this->id = $id;}

    /** @return int */
    public function getUserId(): int{return $this->userId;}

    /** @param int $userId */
    public function setUserId(int $userId): void{$this->userId = $userId;}

    /** @return int */
    public function getCode(): int{return $this->code;}

    /** @param int $code */
    public function setCode(int $code): void{$this->code = $code;}

    /** @return int */
    public function getCreatedAt(): int{return $this->createdAt;}

    /** @param int $createdAt */
    public function setCreatedAt(int $createdAt): void{$this->createdAt = $createdAt;}

    /** @return int */
    public function getExpirationTime(): int{return $this->expirationTime;}

    /** @param int $expirationTime */
    public function setExpirationTime(int $expirationTime): void{$this->expirationTime = $expirationTime;}

    /** @return string|null */
    public function getType(): ?string{return $this->type;}

    /** @param string|null $type */
    public function setType(?string $type): void{$this->type = $type;}
}