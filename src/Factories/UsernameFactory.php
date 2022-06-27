<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

class UsernameFactory
{
    /**
     * @param string $username
     * @return string
     */
    public static function standardiseUsername(
        string $username,
    ): string
    {
        $response = str_replace(' ', '-', trim(strtolower($username)));

        return preg_replace('/[^a-z\d\-\_]/', '', $response);
    }
}