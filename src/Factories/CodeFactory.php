<?php
namespace CarloNicora\Minimalism\Services\Auth\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Auth\Auth;
use CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\CodesTable;
use CarloNicora\Minimalism\Services\Auth\Events\AuthErrorEvents;
use CarloNicora\Minimalism\Services\Encrypter\Encrypter;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class CodeFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var Auth  */
    private Auth $auth;

    /** @var CodesTable  */
    private CodesTable $codes;

    /**
     * CodeFactory constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;

        $this->auth = $services->service(Auth::class);

        /** @var MySQL $mysql */
        $mysql = $this->services->service(MySQL::class);

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->codes = $mysql->create(CodesTable::class);
    }

    /**
     * @param array $user
     * @throws DbSqlException
     * @throws Exception
     */
    public function generateAndSendCode(array $user): void
    {
        $this->codes->purgeExpired();
        $this->codes->purgeUserId($user['userId']);

        try {
            $actualCode = random_int(100000, 999999);
        } catch (Exception $exception) {
            /** @noinspection RandomApiMigrationInspection */
            $actualCode = rand(100000, 999999);
        }

        $code = [
            'userId' => $user['userId'],
            'code' => $actualCode,
            'expirationTime' => date('Y-m-d H:i:s', time() + 60 * 5)
        ];
        $this->codes->update($code);

        $this->sendAccessCode($user, (string)$actualCode);
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
        } catch (DbRecordNotFoundException $e) {
            $this->services->logger()->error()->log(
                AuthErrorEvents::AUTH_CODE_EXPIRED()
            )->throw();
        }
    }

    /**
     * @param array $user
     * @param string $code
     * @throws Exception
     */
    private function sendAccessCode(array $user, string $code): void
    {
        /** @var Encrypter $encrypter */
        $encrypter = $this->services->service(Encrypter::class);

        $data = [];
        $data['title'] = $this->auth->getCodeEmailTitle();
        $data['previewText'] = $this->auth->getCodeEmailTitle();
        $data['username'] = $user['username'];
        $data['code'] = $code;
        $data['loginUrl'] = $this->services->paths()->getUrl()
            . 'login/'
            . 'docodelogin/'
            . $encrypter->encryptId($user['userId']) . '/'
            . $code . '/'
            . $this->auth->getClientId() . '/'
            . $this->auth->getState();

        $emailFactory = new EmailFactory($this->services);
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
}