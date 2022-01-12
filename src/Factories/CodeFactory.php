<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\Mailer\Interfaces\MailerInterface;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Data\User;
use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\CodesTable;
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
     * @param MailerInterface $mailer
     * @throws Exception
     */
    public function __construct(
        private Auth $auth,
        private MySQL $mysql,
        private EncrypterInterface $encrypter,
        private Path $path,
        private MailerInterface $mailer,
    )
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->codes = $this->mysql->create(CodesTable::class);
    }

    /**
     * @param User $user
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['userId' => "mixed", 'code' => "int", 'expirationTime' => "false|string"])] private function
    generateCode(User $user): array
    {
        $this->codes->purgeExpired();
        $this->codes->purgeUserId($user->getId());

        try {
            $actualCode = random_int(100000, 999999);
        } catch (Exception) {
            /** @noinspection RandomApiMigrationInspection */
            $actualCode = rand(100000, 999999);
        }

        $response = [
            'userId' => $user->getId(),
            'code' => $actualCode,
            'expirationTime' => date('Y-m-d H:i:s', time() + 60 * 5)
        ];
        $this->codes->update($response);

        return $response;
    }

    /**
     * @param User $user
     * @throws Exception
     */
    public function generateAndSendCode(User $user): void
    {
        $code = $this->generateCode($user);
        $this->sendAccessCode($user, (string)$code['code']);
    }

    /**
     * @param User $user
     * @throws Exception
     */
    public function generateAndSendResetCode(User $user): void
    {
        $code = $this->generateCode($user);
        $this->sendForgotCode($user, (string)$code['code']);
    }

    /**
     * @param User $user
     * @param int $code
     * @throws Exception
     */
    public function validateCode(User $user, int $code): void
    {
        $this->codes->purgeExpired();

        $codeRecord = $this->codes->userIdCode($user->getId(), $code);

        if ($codeRecord === []){
            throw new RuntimeException('The authorization code is incorrect or expired', 412);
        }

        $codeRecord = $codeRecord[0];

        $this->codes->delete($codeRecord);
    }

    /**
     * @param User $user
     * @param string $code
     * @throws Exception
     */
    private function sendAccessCode(User $user, string $code): void
    {
        $data = [];
        $data['title'] = $this->auth->getCodeEmailTitle();
        $data['previewText'] = $this->auth->getCodeEmailTitle();
        $data['username'] = $user->getName();
        $data['code'] = $code;
        $data['loginUrl'] = $this->path->getUrl()
            . 'code/'
            . $this->encrypter->encryptId($user->getId()) . '/'
            . '0/'
            . $this->auth->getClientId() . '/'
            . $this->auth->getState() . '/'
            . $code;

        $emailFactory = new EmailFactory(
            path: $this->path,
            mailer: $this->mailer,
        );
        $emailFactory->sendEmail(
            'Emails/Logincode.twig',
            $this->auth->getCodeEmailTitle(),
            $user->getEmail(),
            $user->getName(),
            $this->auth->getSenderEmail(),
            $this->auth->getSenderName(),
            $data
        );
    }

    /**
     * @param User $user
     * @param string $code
     * @throws Exception
     */
    private function sendForgotCode(User $user, string $code): void
    {
        $data = [];
        $data['title'] = $this->auth->getForgotEmailTitle();
        $data['previewText'] = $this->auth->getForgotEmailTitle();
        $data['username'] = $user->getName();
        $data['code'] = $code;
        $data['resetUrl'] = $this->path->getUrl()
            . 'change/'
            . $this->encrypter->encryptId($user->getId()) . '/'
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
            $user->getEmail(),
            $user->getName(),
            $this->auth->getSenderEmail(),
            $this->auth->getSenderName(),
            $data
        );
    }
}