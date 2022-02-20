<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Codes\IO;

use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlComparison;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\Databases\CodesTable;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\DataObjects\Code;
use CarloNicora\Minimalism\Services\Auth\Factories\ExceptionFactory;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use Exception;

class CodeIO extends AbstractSqlIO
{
    /**
     * @return void
     * @throws Exception
     */
    public function purgeExpired(
    ): void
    {
        $this->data->delete(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(field: CodesTable::expirationTime, value: date('Y-m-d H:i:s'), comparison: SqlComparison::LesserThan),
        );
    }

    /**
     * @param int $userId
     * @param int $code
     * @return bool
     * @throws Exception
     */
    public function isCodeValid(
        int $userId,
        int $code,
    ): bool
    {
        $this->purgeExpired();

        $recordset = $this->data->read(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(CodesTable::userId, $userId)
                ->addParameter(CodesTable::code, $code),
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

        $recordset = $this->data->read(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(CodesTable::userId, $userId)
                ->addParameter(CodesTable::code, $code),
        );

        $expiration = strtotime($recordset[0]['expirationTime']);
        if ($expiration === false){
            $expiration = time();
        }

        /** @noinspection InsufficientTypesControlInspection */
        if ($recordset === [] || time() > $expiration){
            throw ExceptionFactory::CodeInvalidOrExpired->create();
        }

        $this->data->delete(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(field: CodesTable::userId, value: $userId),
        );
    }

    /**
     * @param int $userId
     * @return string
     * @throws Exception
     */
    public function generateCode(
        int $userId,
    ): string{
        $this->purgeExpired();

        $recordset = $this->data->read(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(CodesTable::userId, $userId),
        );

        if ($recordset === []) {
            try {
                $response = random_int(100000, 999999);
            } catch (Exception) {
                /** @noinspection RandomApiMigrationInspection */
                $response = rand(100000, 999999);
            }

            $code = new Code();
            $code->setUserId($userId);
            $code->setCode($response);
            $code->setExpirationTime(time() + 60 * 5);

            /** @noinspection UnusedFunctionResultInspection */
            $this->data->create(
                queryFactory: $code,
            );
        } else {
            $response = $recordset[0]['code'];
        }

        return $response;
    }
}