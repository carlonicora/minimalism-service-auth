<?php
namespace CarloNicora\Minimalism\Services\Auth\Interfaces;

interface SocialLoginInterface
{
    /**
     * @return string|null
     */
    public function generateLink(): ?string;
}