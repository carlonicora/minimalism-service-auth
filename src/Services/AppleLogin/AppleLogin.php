<?php
namespace CarloNicora\Minimalism\Services\Auth\Services\AppleLogin;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\Auth\Interfaces\SocialLoginInterface;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;

class AppleLogin extends AbstractService implements SocialLoginInterface
{
    /**
     * @param Path $path
     * @param string|null $MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID
     * @param string|null $MINIMALISM_SERVICE_AUTH_APPLE_TEAM_ID
     * @param string|null $MINIMALISM_SERVICE_AUTH_APPLE_KEYFILE_ID
     */
    public function __construct(
        private readonly Path $path,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID=null,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_APPLE_TEAM_ID=null,
        private readonly ?string $MINIMALISM_SERVICE_AUTH_APPLE_KEYFILE_ID=null,
    )
    {
    }

    /** @var string|null  */
    private ?string $appleState=null;

    /**
     * @return string|null
     */
    public function getAppleState(
    ): ?string
    {
        return $this->appleState;
    }

    /**
     * @param string $appleState
     */
    public function setAppleState(
        string $appleState,
    ): void
    {
        $this->appleState = $appleState;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    private function getAppleClientSecret(
    ): ?string
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
     * @return void
     */
    public function initialise(): void {
        if (isset($_SESSION)) {
            $this->appleState = $_SESSION['appleState'] ?? null;
        }
    }

    /**
     * @return void
     */
    public function destroy(): void
    {
        if (isset($_SESSION)) {
            $_SESSION['appleState'] = $this->appleState;
        }
    }

    /**
     * @return void
     */
    protected function cleanAppleData(
    ): void
    {
        $this->appleState = null;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function generateLink(
    ): ?string
    {
        if ($this->MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID === null || $this->getAppleClientSecret() === null) {
            return null;
        }
        $this->setAppleState(bin2hex(random_bytes(5)));

        return 'https://appleid.apple.com/auth/authorize' . '?' . http_build_query([
                'response_type' => 'code',
                'response_mode' => 'form_post',
                'client_id' => $this->MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID,
                'redirect_uri' => $this->path->getUrl() . 'social/apple',
                'state' => $this->getAppleState(),
                'scope' => 'email',
            ]);
    }

    /**
     * @param array $params
     * @return array
     * @throws Exception
     */
    private function httpCall(array $params): array
    {
        $ch = curl_init('https://appleid.apple.com/auth/token');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'Users-Agent: curl',
        ]);

        $response = curl_exec($ch);

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string|null $code
     * @param string|null $state
     * @return array
     * @throws Exception
     */
    public function validateLogin(
        ?string $code=null,
        ?string $state=null,
    ): array
    {
        if ($state !== $this->getAppleState()) {
            header('Location:' . $this->path->getUrl() . 'index?error=' . ExceptionFactory::AppleResponseStateMismatch->value);
            exit;
        }

        $response = $this->httpCall(
            [
                'grant_type' => 'authorization_code',
                'code' => $code ?? '',
                'redirect_uri' => $this->path->getUrl() . 'social/apple',
                'client_id' => $this->MINIMALISM_SERVICE_AUTH_APPLE_CLIENT_ID,
                'client_secret' => $this->getAppleClientSecret(),
            ]
        );

        if (!isset($response['access_token'])) {
            header('Location:' . $this->path->getUrl() . 'index?error=' . ExceptionFactory::AppleResponseMissingToken->value);
            exit;
        }

        $response = explode('.', $response['id_token'])[1];
        return  json_decode(base64_decode($response), true, 512, JSON_THROW_ON_ERROR);
    }
}