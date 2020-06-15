<?php
namespace CarloNicora\Minimalism\Services\Auth;

use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractService;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Core\Services\Interfaces\ServiceConfigurationsInterface;
use CarloNicora\Minimalism\Services\Auth\Configurations\AuthConfigurations;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\AppsTables;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\AuthsTable;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\TokensTable;
use CarloNicora\Minimalism\Services\Auth\Events\AuthErrorEvents;
use CarloNicora\Minimalism\Services\Auth\Interfaces\AuthenticationInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;
use RuntimeException;

class Auth  extends AbstractService {
    /** @var AuthConfigurations  */
    private AuthConfigurations $configData;

    /** @var string|null  */
    private ?string $clientId=null;

    /** @var string|null  */
    private ?string $state=null;

    /** @var int|null */
    private ?int $userId=null;

    /**
     * abstractApiCaller constructor.
     * @param ServiceConfigurationsInterface $configData
     * @param ServicesFactory $services
     */
    public function __construct(ServiceConfigurationsInterface $configData, ServicesFactory $services) {
        parent::__construct($configData, $services);

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->configData = $configData;
    }

    /**
     * @return AuthenticationInterface
     * @throws Exception
     */
    public function getAuthenticationTable(): AuthenticationInterface
    {
        $authClass = $this->configData->getAuthInterfaceClass();

        if ($authClass === null){
            $this->services->logger()->error()->log(
                AuthErrorEvents::AUTH_INTERFACE_NOT_CONFIGURED()
            )->throw();
        }

        /** @var AuthenticationInterface $response */
        $response = new $authClass($this->services);

        return $response;
    }

    /**
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @param string|null $clientId
     */
    public function setClientId(?string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     */
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @param int $appId
     * @return array
     * @throws DbSqlException|Exception
     */
    public function generateAuth(int $appId): array
    {
        $response = [
            'appId' => $appId,
            'userId' => $this->userId,
            'code' => bin2hex(random_bytes(32)),
            'expiration' => date('Y-m-d H:i:s', time() + 30)
        ];

        /** @var MySQL $mysql */
        $mysql = $this->services->service(MySQL::class);
        $auths = $mysql->create(AuthsTable::class);

        $auths->update($response);

        return $response;
    }

    /**
     * @param array $app
     * @param array $auth
     * @return string
     */
    public function generateRedirection(array $app, array $auth): string
    {
        $response = $app['url'];

        $join = (strpos($response, '?') !== false) ? '&' : '?';
        $response .= $join
            . 'code=' . $auth['code']
            . '&state=' . $this->state;

        return $response;
    }

    /**
     * @return array
     * @throws DbSqlException
     * @throws DbRecordNotFoundException|Exception
     */
    public function getAppByClientId(): array
    {
        /** @var MySQL $mysql */
        $mysql = $this->services->service(MySQL::class);
        /** @var AppsTables $apps */
        $apps = $mysql->create(AppsTables::class);
        return $apps->getByClientId($this->clientId);
    }

    /**
     * @param string $token
     * @return int
     * @throws Exception
     */
    public function validateToken(string $token): ?int
    {
        /** @var MySQL $mysql */
        $mysql = $this->services->service(MySQL::class);
        /** @var TokensTable $tokens */
        $tokens = $mysql->create(TokensTable::class);

        try {
            $tokenArray = $tokens->loadByToken($token);
            return $tokenArray['userId'];
        } catch (DbRecordNotFoundException|DbSqlException $e) {
            throw new RuntimeException('token not found');
        }
    }
}