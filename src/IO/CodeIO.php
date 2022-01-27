<?php
namespace CarloNicora\Minimalism\Services\Auth\IO;

use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\CodesTable;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use Exception;

class CodeIO extends AbstractLoader
{
    /**
     * @return void
     */
    public function purgeExpired(
    ): void
    {
        /** @noinspection UnusedFunctionResultInspection */
        /** @see CodesTable::purgeExpired() */
        $this->data->run(
            tableInterfaceClassName: CodesTable::class,
            functionName: 'purgeExpired',
            parameters: [],
        );
    }

    /**
     * @param int $userId
     * @param int $code
     * @return bool
     */
    public function isCodeValid(
        int $userId,
        int $code,
    ): bool
    {
        $this->purgeExpired();

        /** @see CodesTable::readByUserIdCode() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CodesTable::class,
            functionName: 'readByUserIdCode',
            parameters: [$userId, $code],
        );

        return $recordset !== [];
    }

    /**
     * @param int $userId
     * @param int $code
     * @return void
     * @throws Exception
     */
    public function validate(
        int $userId,
        int $code,
    ): void
    {
        $this->purgeExpired();

        /** @see CodesTable::readByUserIdCode() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CodesTable::class,
            functionName: 'readByUserIdCode',
            parameters: [$userId, $code],
        );

        if ($recordset === [] || strtotime($recordset[0]['expirationTime']) > time()){
            throw ExceptionFactory::CodeInvalidOrExpired->create();
        }

        /** @noinspection UnusedFunctionResultInspection */
        /** @see CodesTable::purgeUserId() */
        $this->data->run(
            tableInterfaceClassName: CodesTable::class,
            functionName: 'purgeUserId',
            parameters: [$userId],
        );
    }

    /**
     * @param int $userId
     * @return string
     */
    public function generateCode(
        int $userId,
    ): string{
        $this->purgeExpired();

        /** @see CodesTable::readByUserId() */
        $recordset = $this->data->read(
            tableInterfaceClassName: CodesTable::class,
            functionName: 'readByUserId',
            parameters: [$userId],
        );

        if ($recordset === []) {
            try {
                $response = random_int(100000, 999999);
            } catch (Exception) {
                /** @noinspection RandomApiMigrationInspection */
                $response = rand(100000, 999999);
            }

            $codeRecord = [
                'userId' => $userId,
                'code' => $response,
                'expirationTime' => date('Y-m-d H:i:s', time() + 60 * 5)
            ];

            /** @noinspection UnusedFunctionResultInspection */
            $this->data->insert(
                tableInterfaceClassName: CodesTable::class,
                records: [$codeRecord]
            );
        } else {
            $response = $recordset[0]['code'];
        }

        return $response;
    }
}