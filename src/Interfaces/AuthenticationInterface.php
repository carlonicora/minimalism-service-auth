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
     * @param string $email
     * @return array
     */
    public function generateNewUser(string $email): array;

    /**
     * @param array $user
     */
    public function activateUser(array $user): void;
}