<?php
namespace CarloNicora\Minimalism\Services\Auth;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Interfaces\Security\Interfaces\SecurityInterface;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\AppsTables;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\AuthsTable;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\TokensTable;
use CarloNicora\Minimalism\Services\Auth\Interfaces\AuthenticationInterface;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use RuntimeException;

class Auth extends AbstractService implements SecurityInterface
{
    /** @var array|null */
    protected ?array $headers = null;

    /** @var string|null  */
    private ?string $client_id=null;

    /** @var string|null  */
    private ?string $state=null;

    /** @var string|null  */
    private ?string $salt=null;

    /** @var int|null */
    private ?int $userId=null;

    /** @var bool  */
    private bool $isUser=false;

    /** @var bool  */
    private bool $twoFactorValidationConfirmed=false;

    /** @var bool  */
    private bool $isNewRegistration=false;

    /** @var string|null  */
    private ?string $appleState=null;

    /** @var AuthenticationInterface|null  */
    private ?AuthenticationInterface $authInterfaceClass=null;

    /**
     * Auth constructor.
     * @param MySQL $mysql
     * @param Path $path
     * @param string|null $MINIMALISM_SERVICE_AUTH_SENDER_NAME
     * @param string|null $MINIMALISM_SERVICE_AUTH_SENDER_EMAIL
     * @param string|null $MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE
     * @param string|null $MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE
     * @param string|null $MINIMALISM_SERVICE_AUTH_FACEBOOK_ID
     * @param string|null $MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET
     * @param string|null $MINIMALISM_SERVICE_AUTH_GOOGLE_IDENTITY_FILE
     * @param string|null $MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID
     * @param string|null $MINIMALISM_SERVICE_AUTH_APPLE_TEAM_ID
     * @param string|null $MINIMALISM_SERVICE_AUTH_APPLE_KEYFILE_ID
     */
    public function __construct(
        private MySQL $mysql,
        private Path $path,
        private ?string $MINIMALISM_SERVICE_AUTH_SENDER_NAME=null,
        private ?string $MINIMALISM_SERVICE_AUTH_SENDER_EMAIL=null,
        private ?string $MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE='',
        private ?string $MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE='',
        private ?string $MINIMALISM_SERVICE_AUTH_FACEBOOK_ID=null,
        private ?string $MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET=null,
        private ?string $MINIMALISM_SERVICE_AUTH_GOOGLE_IDENTITY_FILE=null,
        private ?string $MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID=null,
        private ?string $MINIMALISM_SERVICE_AUTH_APPLE_TEAM_ID=null,
        private ?string $MINIMALISM_SERVICE_AUTH_APPLE_KEYFILE_ID=null,
    )
    {
        parent::__construct();
    }

    /**
     * @return string|null
     */
    public static function getBaseInterface(
    ): ?string
    {
        return SecurityInterface::class;
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
            throw new RuntimeException('The authorization interface is not configured', 500);
        }

        return $this->authInterfaceClass;
    }

    /**
     *
     */
    public function cleanData(): void
    {
        $this->client_id = null;
        $this->state = null;
        $this->appleState = null;
        $this->userId = null;
        $this->isUser = false;
        $this->salt = null;
        $this->twoFactorValidationConfirmed = false;
    }

    /**
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->client_id;
    }

    /**
     * @param string|null $client_id
     */
    public function setClientId(?string $client_id): void
    {
        $this->client_id = $client_id;
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
     * @return string|null
     */
    public function getSalt(
    ): ?string
    {
        return $this->salt;
    }

    /**
     * @param string|null $salt
     */
    public function setSalt(
        ?string $salt,
    ): void
    {
        $this->salt = $salt;
    }

    /**
     * @return void
     */
    public function set2faValiationConfirmed(
    ): void
    {
        $this->twoFactorValidationConfirmed = true;
    }

    /**
     * @return bool
     */
    public function isTwoFactorValidationConfirmed(
    ): bool
    {
        return $this->twoFactorValidationConfirmed;
    }

    /**
     * @return string|null
     */
    public function getAppleState(): ?string
    {
        return $this->appleState;
    }

    /**
     * @param string $appleState
     */
    public function setAppleState(string $appleState): void
    {
        $this->appleState = $appleState;
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
     *
     */
    public function setIsNewRegistration(): void
    {
        $this->isNewRegistration = true;
    }

    /**
     * @return bool
     */
    public function isNewRegistration(): bool
    {
        return $this->isNewRegistration;
    }

    /**
     * @param int $appId
     * @return array
     * @throws Exception|Exception
     */
    public function generateAuth(int $appId): array
    {
        $response = [
            'appId' => $appId,
            'userId' => $this->userId,
            'code' => bin2hex(random_bytes(32)),
            'expiration' => date('Y-m-d H:i:s', time() + 30)
        ];

        $auths = $this->mysql->create(AuthsTable::class);

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

        $join = (str_contains($response, '?')) ? '&' : '?';
        $response .= $join
            . 'code=' . $auth['code']
            . '&state=' . $this->state
            . '&newRegistration=' . $this->isNewRegistration;

        return $response;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAppByClientId(): array
    {
        /** @var AppsTables $apps */
        $apps = $this->mysql->create(AppsTables::class);
        return $apps->getByClientId($this->client_id);
    }

    /**
     * @param string $token
     * @param bool $isUser
     * @return int|null
     * @throws Exception
     */
    public function validateToken(string $token, bool &$isUser): ?int
    {
        /** @var TokensTable $tokens */
        $tokens = $this->mysql->create(TokensTable::class);

        $tokenArray = $tokens->loadByToken($token);

        if ($tokenArray === []){
            throw new RuntimeException('token not found', 401);
        }
        $tokenArray = $tokenArray[0];

        $isUser = $tokenArray['isUser'];
        return $tokenArray['userId'];
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getAppByToken(
    ): ?array
    {
        /** @var AppsTables $apps */
        $apps = $this->mysql->create(AppsTables::class);
        $app = $apps->readByToken($this->getToken());

        if ($app === [] || count($app) > 1){
            return null;
        }

        return $app[0];
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getUserIdByToken(
    ): ?int
    {
        /** @var TokensTable $tokens */
        $tokens = $this->mysql->create(TokensTable::class);
        $token = $tokens->loadByToken($this->getToken());

        if ($token === [] || count($token) > 1){
            return null;
        }

        return $token[0]['userId'];
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        $bearer = $this->getHeader('Authorization');

        if ($bearer === null){
            return null;
        }

        [,$token] = explode(' ', $bearer);

        if (empty($token)) {
            return null;
        }

        return $token;
    }

    /**
     * @param string|null $verb
     * @param string|null $uri
     * @param array|null $body
     * @return bool
     * @throws Exception
     */
    public function isSignatureValid(
        ?string $verb=null,
        ?string $uri=null,
        ?array $body=null,
    ) : bool
    {
        $token = $this->getToken();

        if ($token === null){
            return false;
        }

        $this->userId = $this->validateToken($token, $this->isUser);

        return true;
    }

    /**
     * @return string
     */
    public function getSenderName(): string
    {
        if ($this->MINIMALISM_SERVICE_AUTH_SENDER_NAME === null){
            throw new RuntimeException('Auth sender name not configured', 500);
        }
        return $this->MINIMALISM_SERVICE_AUTH_SENDER_NAME;
    }

    /**
     * @return string
     */
    public function getSenderEmail(): string
    {
        if ($this->MINIMALISM_SERVICE_AUTH_SENDER_EMAIL === null){
            throw new RuntimeException('Auth sender email not configured', 500);
        }
        return $this->MINIMALISM_SERVICE_AUTH_SENDER_EMAIL;
    }

    /**
     * @return string
     */
    public function getCodeEmailTitle(): string
    {
        return $this->MINIMALISM_SERVICE_AUTH_CODE_EMAIL_TITLE;
    }

    /**
     * @return string
     */
    public function getForgotEmailTitle(): string
    {
        return $this->MINIMALISM_SERVICE_AUTH_FORGOT_EMAIL_TITLE;
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
        return $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_ID;
    }

    /**
     * @return string|null
     */
    public function getFacebookSecret(): ?string
    {
        return $this->MINIMALISM_SERVICE_AUTH_FACEBOOK_SECRET;
    }

    /**
     * @return string|null
     */
    public function getGoogleIdentityFile(): ?string
    {
        return $this->MINIMALISM_SERVICE_AUTH_GOOGLE_IDENTITY_FILE;
    }

    /**
     * @return string|null
     */
    public function getAppleClientId(): ?string
    {
        return $this->MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID;
    }

    /**
     * @return string|null
     */
    public function getAppleClientSecret(): ?string
    {
        if (empty($this->MINIMALISM_SERVICE_AUTH_APPLE_KEYFILE_ID)) {
            return null;
        }

        $keyFileName = $this->path->getRoot()
            . DIRECTORY_SEPARATOR
            . 'AuthKey_' . $this->MINIMALISM_SERVICE_AUTH_APPLE_KEYFILE_ID . '.p8';

        $algorithmManager = new AlgorithmManager([new ES256()]);

        $jwsBuilder = new JWSBuilder($algorithmManager);
        try {
            $jws = $jwsBuilder
                ->create()
                ->withPayload(json_encode([
                    'iat' => time(),
                    'exp' => time() + 3600,
                    'iss' => $this->MINIMALISM_SERVICE_AUTH_APPLE_TEAM_ID,
                    'aud' => 'https://appleid.apple.com',
                    'sub' => $this->MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID
                ], JSON_THROW_ON_ERROR))
                ->addSignature(JWKFactory::createFromKeyFile($keyFileName), [
                    'alg' => 'ES256',
                    'kid' => $this->MINIMALISM_SERVICE_AUTH_APPLE_KEYFILE_ID
                ])
                ->build();
        } catch (Exception) {
            return '';
        }

        return (new CompactSerializer())->serialize($jws, 0);
    }

    /**
     * @param string $headerName
     * @return string|null
     */
    public function getHeader(string $headerName): ?string
    {
        $this->headers = getallheaders();

        return $this->headers[$headerName] ?? null;
    }

    /**
     * @throws Exception
     */
    public function initialise(): void {
        if (isset($_SESSION)) {
            $this->client_id = $_SESSION['client_id'] ?? null;
            $this->state = $_SESSION['state'] ?? null;
            $this->salt = $_SESSION['salt'] ?? null;
            $this->userId = $_SESSION['userId'] ?? null;
            $this->appleState = $_SESSION['appleState'] ?? null;
            $this->isUser = array_key_exists('isUser', $_SESSION)
                ? $_SESSION['isUser']
                : false;
            $this->isNewRegistration = array_key_exists('isNewRegistration', $_SESSION)
                ? $_SESSION['isNewRegistration']
                : false;
            $this->twoFactorValidationConfirmed = array_key_exists('2faConfirmed', $_SESSION)
                ? $_SESSION['2faConfirmed']
                : false;
        }
    }

    /**
     *
     */
    public function destroy(): void
    {
        parent::destroy();
        if (isset($_SESSION)) {
            $_SESSION['userId'] = $this->userId;
            $_SESSION['client_id'] = $this->client_id;
            $_SESSION['salt'] = $this->salt;
            $_SESSION['state'] = $this->state;
            $_SESSION['appleState'] = $this->appleState;
            $_SESSION['isUser'] = $this->isUser();
            $_SESSION['isNewRegistration'] = $this->isNewRegistration();
            $_SESSION['2faConfirmed'] = $this->twoFactorValidationConfirmed;
        }
    }
}

// @codeCoverageIgnoreStart
if (!function_exists('getallheaders'))
{
    // @codeCoverageIgnoreEnd
    function getallheaders(): array
    {
        $headers = [];
        foreach ($_SERVER ?? [] as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
    // @codeCoverageIgnoreStart
}
// @codeCoverageIgnoreEnd