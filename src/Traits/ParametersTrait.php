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

    /** @var bool  */
    private bool $isAuthenticated=false;

    /** @var string|null  */
    private ?string $source=null;

    /** @var string|null  */
    private ?string $email=null;

    /** @var bool  */
    private bool $saveInSession=true;

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
     *
     */
    public function resetIsNewRegistration(
    ): void
    {
        $this->isNewRegistration = false;
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
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->isAuthenticated;
    }

    /**
     * @param bool $isAuthenticated
     */
    public function setIsAuthenticated(
        bool $isAuthenticated,
    ): void
    {
        $this->isAuthenticated = $isAuthenticated;
    }

    /**
     * @return string|null
     */
    public function getSource(
    ): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     */
    public function setSource(
        ?string $source
    ): void
    {
        $this->source = $source;
    }

    /**
     * @return string|null
     */
    public function getEmail(
    ): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(
        ?string $email,
    ): void
    {
        $this->email = $email;
    }

    /**
     * @return void
     */
    public function allowSaveInSession(
    ): void
    {
        $this->saveInSession = true;
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
        $this->source = $_SESSION['source'] ?? null;
        $this->email = $_SESSION['email'] ?? null;
        $this->isAuthenticated = array_key_exists('isAuthenticated', $_SESSION) ? $_SESSION['isAuthenticated'] : false;
        $this->isNewRegistration = array_key_exists('isNewRegistration', $_SESSION) ? $_SESSION['isNewRegistration'] : false;
    }

    /**
     * @return void
     */
    protected function saveParametersInSession(
    ): void
    {
        if ($this->saveInSession) {
            $_SESSION['userId'] = $this->userId;
            $_SESSION['client_id'] = $this->client_id;
            $_SESSION['state'] = $this->state;
            $_SESSION['source'] = $this->source;
            $_SESSION['email'] = $this->email;
            $_SESSION['isAuthenticated'] = $this->isAuthenticated;
            $_SESSION['isNewRegistration'] = $this->isNewRegistration;
        }

        $this->client_id = null;
        $this->state = null;
        $this->userId = null;
        $this->source = null;
        $this->email = null;
        $this->isNewRegistration = false;
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
        $this->source = null;
        $this->email = null;
        $this->isNewRegistration = false;
        $this->isAuthenticated = false;

        unset($_SESSION['userId'],$_SESSION['client_id'],$_SESSION['state'],$_SESSION['source'],$_SESSION['email'],$_SESSION['isAuthenticated'],$_SESSION['isNewRegistration']);
        $this->saveInSession = false;
    }
}