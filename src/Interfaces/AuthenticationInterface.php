<?php
namespace CarloNicora\Minimalism\Services\Auth\Interfaces;

interface AuthenticationInterface
{
    /**
     * @param string $email
     * @return array
     */
    public function authenticateByEmail(string $email): array;
}