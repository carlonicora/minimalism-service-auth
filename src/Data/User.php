<?php
namespace CarloNicora\Minimalism\Services\Auth\Data;

class User
{
    /** @var bool  */
    private bool $isSocialLogin=false;

    /**
     * @param int $id
     * @param string $username
     * @param string $email
     * @param string|null $name
     * @param string|null $password
     * @param bool $isActive
     */
    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        private ?string $name=null,
        private ?string $password=null,
        private readonly bool $isActive=false,
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
     * @param string $username
     */
    public function setUsername(
        string $username,
    ): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername(
    ): string
    {
        return $this->username;
    }

    /**
     * @param string $name
     */
    public function setName(
        string $name
    ): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getName(
    ): ?string
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
     * @return bool
     */
    public function isActive(
    ): bool
    {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    public function isSocialLogin(
    ): bool
    {
        return $this->isSocialLogin;
    }

    /**
     * @param bool $isSocialLogin
     */
    public function setIsSocialLogin(
        bool $isSocialLogin,
    ): void
    {
        $this->isSocialLogin = $isSocialLogin;
    }
}