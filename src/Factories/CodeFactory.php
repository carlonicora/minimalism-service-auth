<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;

class CodeFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /**
     * CodeFactory constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    public function generateAndSendCode(array $user): void
    {
        //DELETE all the user's or expired codes
        //CREATE a new code
        //EMAIL the code
    }

    public function validateCode(array $user, int $code): void
    {
        //FIND CODE
        //DELETE code
        //THROW error if not found of expired
    }
}