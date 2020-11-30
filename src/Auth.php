<?php
namespace CarloNicora\Minimalism\Services\Auth;

use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractService;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Core\Services\Interfaces\ServiceConfigurationsInterface;
use CarloNicora\Minimalism\Core\Traits\HttpHeadersTrait;
use CarloNicora\Minimalism\Interfaces\SecurityInterface;
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

class Auth  extends AbstractService implements SecurityInterface
{
    use HttpHeadersTrait;

    /** @var AuthConfigurations  */
    private AuthConfigurations $configData;

    /** @var string|null  */
    private ?string $clientId=null;

    /** @var string|null  */
    private ?string $state=null;

    /** @var int|null */
    private ?int $userId=null;

    /** @var bool  */
    private bool $isUser=false;

    /** @var AuthenticationInterface|null  */
    private ?AuthenticationInterface $authInterfaceClass=null;

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
     * @param AuthenticationInterface|null $authInterfaceClass
     */
    public function setAuthInterfaceClass(?AuthenticationInterface $authInterfaceClass): void
    {
        $this->authInterfaceClass = $authInterfaceClass;
    }

    /**
     * @return AuthenticationInterface
     * @throws Exception
     */
    public function getAuthenticationTable(): AuthenticationInterface
    {
        if ($this->authInterfaceClass === null){
            $this->services->logger()->error()->log(
                AuthErrorEvents::AUTH_INTERFACE_NOT_CONFIGURED()
            )->throw();
        }

        return $this->authInterfaceClass;
    }

    /**
     *
     */
    public function cleanData(): void 
    {
        $this->clientId = null;
        $this->state = null;
        $this->userId = null;
        $this->isUser = false;
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
     * @param bool $isUser
     * @return int
     * @throws Exception
     */
    public function validateToken(string $token, bool &$isUser): ?int
    {
        /** @var MySQL $mysql */
        $mysql = $this->services->service(MySQL::class);
        /** @var TokensTable $tokens */
        $tokens = $mysql->create(TokensTable::class);

        try {
            $tokenArray = $tokens->loadByToken($token);
            $isUser = $tokenArray['isUser'];
            return $tokenArray['userId'];
        } catch (DbRecordNotFoundException|DbSqlException $e) {
            throw new RuntimeException('token not found', 401);
        }
    }

    /**
     * @param string $verb
     * @param string $uri
     * @param array|null $body
     * @return bool
     * @throws Exception
     */
    public function isSignatureValid(string $verb, string $uri, array $body = null): bool
    {
        $bearer = $this->getHeader('Authorization');

        if ($bearer === null){
            return false;
        }

        [,$token] = explode(' ', $bearer);

        if (empty($token)) {
            return false;
        }

        $this->userId = $this->validateToken($token, $this->isUser);

        return true;
    }

    /**
     * @return SecurityInterface
     */
    public function getSecurityInterface(): SecurityInterface
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getSenderName(): string
    {
        return $this->configData->getSenderName();
    }

    /**
     * @return string
     */
    public function getSenderEmail(): string
    {
        return $this->configData->getSenderEmail();
    }

    /**
     * @return string
     */
    public function getCodeEmailTitle(): string
    {
        return $this->configData->getCodeEmailTitle();
    }

    /**
     * @return string
     */
    public function getForgotEmailTitle(): string
    {
        return $this->configData->getForgotEmailTitle();
    }

    /**
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->isUser;
    }

    /**
     * @return string|null
     */
    public function getFacebookId(): ?string
    {
        return $this->configData->getFacebookId();
    }

    /**
     * @return string|null
     */
    public function getFacebookSecret(): ?string
    {
        return $this->configData->getFacebookSecret();
    }

    /**
     * @return string|null
     */
    public function getGoogleIdentityFile(): ?string
    {
        return $this->configData->getGoogleIdentityFile();
    }

    /**
     * @return string|null
     */
    public function getAppleClientId(): ?string
    {
        return $this->configData->getAppleClientId();
    }

    /**
     * @return string|null
     */
    public function getAppleClientSecret(): ?string
    {
        return $this->configData->getAppleClientSecret();
    }
}