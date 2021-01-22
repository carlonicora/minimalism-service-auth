<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\CodesTable;
use CarloNicora\Minimalism\Services\Mailer\Mailer;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use RuntimeException;

class CodeFactory
{
    /** @var CodesTable  */
    private CodesTable $codes;

    /**
     * CodeFactory constructor.
     * @param Auth $auth
     * @param MySQL $mysql
     * @param EncrypterInterface $encrypter
     * @param Path $path
     * @param Mailer $mailer
     * @throws Exception
     */
    public function __construct(
        private Auth $auth,
        private MySQL $mysql,
        private EncrypterInterface $encrypter,
        private Path $path,
        private Mailer $mailer,
    )
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->codes = $this->mysql->create(CodesTable::class);
    }

    /**
     * @param array $user
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['userId' => "mixed", 'code' => "int", 'expirationTime' => "false|string"])] private function
    generateCode(array $user): array
    {
        $this->codes->purgeExpired();
        $this->codes->purgeUserId($user['userId']);

        try {
            $actualCode = random_int(100000, 999999);
        } catch (Exception) {
            /** @noinspection RandomApiMigrationInspection */
            $actualCode = rand(100000, 999999);
        }

        $response = [
            'userId' => $user['userId'],
            'code' => $actualCode,
            'expirationTime' => date('Y-m-d H:i:s', time() + 60 * 5)
        ];
        $this->codes->update($response);

        return $response;
    }

    /**
     * @param array $user
     * @throws Exception
     */
    public function generateAndSendCode(array $user): void
    {
        $code = $this->generateCode($user);
        $this->sendAccessCode($user, (string)$code['code']);
    }

    /**
     * @param array $user
     * @throws Exception
     */
    public function generateAndSendResetCode(array $user): void
    {
        $code = $this->generateCode($user);
        $this->sendForgotCode($user, (string)$code['code']);
    }

    /**
     * @param array $user
     * @param int $code
     * @throws Exception
     */
    public function validateCode(array $user, int $code): void
    {
        $this->codes->purgeExpired();

        try {
            $codeRecord = $this->codes->userIdCode($user['userId'], $code);

            $this->codes->delete($codeRecord);
        } catch (RecordNotFoundException) {
            throw new RuntimeException('The authorization code is incorrect or expired', 412);
        }
    }

    /**
     * @param array $user
     * @param string $code
     * @throws Exception
     */
    private function sendAccessCode(array $user, string $code): void
    {
        $data = [];
        $data['title'] = $this->auth->getCodeEmailTitle();
        $data['previewText'] = $this->auth->getCodeEmailTitle();
        $data['username'] = $user['username'];
        $data['code'] = $code;
        $data['loginUrl'] = $this->path->getUrl()
            . 'login/'
            . 'docodelogin/'
            . $this->encrypter->encryptId($user['userId']) . '/'
            . $code . '/'
            . $this->auth->getClientId() . '/'
            . $this->auth->getState();

        $emailFactory = new EmailFactory(
            path: $this->path,
            mailer: $this->mailer,
        );
        $emailFactory->sendEmail(
            'Emails/Logincode.twig',
            $this->auth->getCodeEmailTitle(),
            $user['email'],
            $user['username'],
            $this->auth->getSenderEmail(),
            $this->auth->getSenderName(),
            $data
        );
    }

    /**
     * @param array $user
     * @param string $code
     * @throws Exception
     */
    private function sendForgotCode(array $user, string $code): void
    {
        $data = [];
        $data['title'] = $this->auth->getForgotEmailTitle();
        $data['previewText'] = $this->auth->getForgotEmailTitle();
        $data['username'] = $user['username'];
        $data['code'] = $code;
        $data['resetUrl'] = $this->path->getUrl()
            . 'change/'
            . $this->encrypter->encryptId($user['userId']) . '/'
            . $code . '/'
            . $this->auth->getClientId() . '/'
            . $this->auth->getState();



        $emailFactory = new EmailFactory(
            path: $this->path,
            mailer: $this->mailer,
        );
        $emailFactory->sendEmail(
            'Emails/Forgot.twig',
            $this->auth->getForgotEmailTitle(),
            $user['email'],
            $user['username'],
            $this->auth->getSenderEmail(),
            $this->auth->getSenderName(),
            $data
        );
    }
}