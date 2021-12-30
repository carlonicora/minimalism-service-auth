<?php
namespace CarloNicora\Minimalism\Services\Auth\Data;

class User
{
    /**
     * @param int $id
     * @param string $name
     * @param string $email
     * @param string|null $password
     * @param string|null $salt
     * @param bool $isActive
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
        private ?string $password=null,
        private ?string $salt=null,
        private bool $isActive=false,
    )
    {
    }

    /**
     * @param int $id
     */
    public function setId(
        int $id,
    ): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(
    ): int
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName(
        string $name,
    ): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(
    ): string
    {
        return $this->name;
    }

    /**
     * @param string $email
     */
    public function setEmail(
        string $email,
    ): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail(
    ): string
    {
        return $this->email;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(
        ?string $password,
    ): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getPassword(
    ): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $salt
     */
    public function setSalt(
        ?string $salt,
    ): void
    {
        $this->salt = $salt;
    }

    /**
     * @return string|null
     */
    public function getSalt(
    ): ?string
    {
        return $this->salt;
    }

    /**
     * @return bool
     */
    public function isActive(
    ): bool
    {
        return $this->isActive;
    }
}