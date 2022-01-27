<?php
namespace CarloNicora\Minimalism\Services\Auth\Traits;

trait ParametersTrait
{
    /** @var string|null  */
    private ?string $client_id=null;

    /** @var string|null  */
    private ?string $state=null;

    /** @var bool  */
    private bool $isNewRegistration=false;

    /** @var int|null */
    private ?int $userId=null;

    /**
     * @return string|null
     */
    public function getClientId(
    ): ?string
    {
        return $this->client_id;
    }

    /**
     * @param string|null $client_id
     */
    public function setClientId(
        ?string $client_id,
    ): void
    {
        $this->client_id = $client_id;
    }

    /**
     * @return string|null
     */
    public function getState(
    ): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(
        ?string $state,
    ): void
    {
        $this->state = $state;
    }

    /**
     *
     */
    public function setIsNewRegistration(
    ): void
    {
        $this->isNewRegistration = true;
    }

    /**
     * @return bool
     */
    public function isNewRegistration(
    ): bool
    {
        return $this->isNewRegistration;
    }

    /**
     * @return int|null
     */
    public function getUserId(
    ): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     */
    public function setUserId(
        ?int $userId,
    ): void
    {
        $this->userId = $userId;
    }

    /**
     * @return void
     */
    protected function readParametersFromSession(
    ): void
    {
        $this->client_id = $_SESSION['client_id'] ?? null;
        $this->state = $_SESSION['state'] ?? null;
        $this->userId = $_SESSION['userId'] ?? null;
        $this->isNewRegistration = array_key_exists('isNewRegistration', $_SESSION)
            ? $_SESSION['isNewRegistration']
            : false;
    }

    /**
     * @return void
     */
    protected function saveParametersInSession(
    ): void
    {
        $_SESSION['userId'] = $this->userId;
        $_SESSION['client_id'] = $this->client_id;
        $_SESSION['state'] = $this->state;
        $_SESSION['isNewRegistration'] = $this->isNewRegistration;
    }

    /**
     * @return void
     */
    protected function cleanParametersData(
    ): void
    {
        $this->client_id = null;
        $this->state = null;
        $this->userId = null;
        $this->isNewRegistration = false;
    }
}