<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\OneTimePasswordBackups\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\Auth\Data\OneTimePasswordBackups\Databases\OneTimePasswordsBackupTable;

#[DbTable(tableClass: OneTimePasswordsBackupTable::class)]
class OneTimePasswordBackup implements SqlDataObjectInterface
{
    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: OneTimePasswordsBackupTable::oneTimePasswordsBackupId)]
    private int $id;

    /** @var int */
    #[DbField]
    private int $userId;

    /** @var string */
    #[DbField]
    private string $otp;

    /** @var bool */
    #[DbField(fieldType: DbFieldType::Bool)]
    private bool $hasBeenUsed;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @return int */
    public function getId(): int{return $this->id;}

    /** @param int $id */
    public function setId(int $id): void{$this->id = $id;}

    /** @return int */
    public function getUserId(): int{return $this->userId;}

    /** @param int $userId */
    public function setUserId(int $userId): void{$this->userId = $userId;}

    /** @return string */
    public function getOtp(): string{return $this->otp;}

    /** @param string $otp */
    public function setOtp(string $otp): void{$this->otp = $otp;}

    /** @return bool */
    public function hasBeenUsed(): bool{return $this->hasBeenUsed;}

    /** @param bool $hasBeenUsed */
    public function setHasBeenUsed(bool $hasBeenUsed): void{$this->hasBeenUsed = $hasBeenUsed;}

    /** @return int */
    public function getCreatedAt(): int{return $this->createdAt;}

    /** @param int $createdAt */
    public function setCreatedAt(int $createdAt): void{$this->createdAt = $createdAt;}
}