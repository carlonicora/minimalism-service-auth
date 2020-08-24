<?php
namespace CarloNicora\Minimalism\Services\Auth\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;

interface AuthenticationInterface
{
    /**
     * AuthenticationInterface constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services);

    /**
     * @param string $email
     * @return array
     */
    public function authenticateByEmail(string $email): ?array;

    /**
     * @param int $userId
     * @return array|null
     */
    public function authenticateById(int $userId): ?array;

    /**
     * @param int $userId
     * @param string $password
     */
    public function updatePassword(int $userId, string $password): void;

    /**
     * @param string $email
     * @param string|null $name
     * @param string|null $provider
     * @return array
     */
    public function generateNewUser(string $email, string $name=null, string $provider=null): array;

    /**
     * @param array $user
     */
    public function activateUser(array $user): void;
}