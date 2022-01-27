<?php
namespace CarloNicora\Minimalism\Services\Auth\Interfaces;

use CarloNicora\Minimalism\Services\Auth\Data\User;

interface AuthenticationInterface
{
    /**
     * @param string $email
     * @return User
     */
    public function authenticateByEmail(string $email): User;

    /**
     * @param int $userId
     * @return User
     */
    public function authenticateById(int $userId): User;

    /**
     * @param int $userId
     * @param string $password
     */
    public function updatePassword(int $userId, string $password): void;

    /**
     * @param int $userId
     * @param string $password
     */
    public function updateUsername(int $userId, string $password): void;

    /**
     * @param string $email
     * @param string|null $name
     * @param string|null $provider
     * @return User
     */
    public function generateNewUser(string $email, ?string $name=null, ?string $provider=null): User;

    /**
     * @param User $user
     */
    public function activateUser(User $user): void;
}