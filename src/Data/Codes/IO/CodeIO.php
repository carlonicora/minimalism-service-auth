<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Codes\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlComparison;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\Databases\CodesTable;
use CarloNicora\Minimalism\Services\Auth\Data\Codes\DataObjects\Code;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use Exception;

class CodeIO extends AbstractSqlIO
{
    /**
     * @throws MinimalismException
     */
    public function purgeExpired(
    ) : void
    {
        $this->data->delete(
            queryFactory: SqlQueryFactory::create(CodesTable::class)->delete()
                ->addParameter(field: CodesTable::expirationTime, value: time(), comparison: SqlComparison::LesserThan)
        );
    }

    /**
     * @param int $userId
     * @return Code[]
     * @throws MinimalismException
     */
    public function readByUserId(
        int $userId,
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(field: CodesTable::userId, value: $userId),
            responseType: Code::class,
            requireObjectsList: true,
        );
    }

    /**
     * @param int $userId
     * @throws Exception
     */
    public function purgeUserId(
        int $userId,
    ) : void
    {
        $this->data->delete(
            queryFactory: SqlQueryFactory::create(CodesTable::class)->delete()
                ->addParameter(field: CodesTable::userId, value: $userId)
        );
    }

    /**
     * @param int $userId
     * @param int $code
     * @return array
     * @throws Exception
     */
    public function readByUserIdCode(
        int $userId,
        int $code,
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(field: CodesTable::userId, value: $userId)
                ->addParameter(field: CodesTable::code, value: $code),
            responseType: Code::class,
            requireObjectsList: true,
        );
    }

    /**
     * @param int $userId
     * @param int $code
     * @return bool
     * @throws MinimalismException
     */
    public function isCodeValid(
        int $userId,
        int $code,
    ): bool
    {
        $this->purgeExpired();

        $recordset = $this->data->read(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(field: CodesTable::userId, value: $userId)
                ->addParameter(field: CodesTable::code, value: $code)
                ->addParameter(field: CodesTable::expirationTime, value: time(), comparison: SqlComparison::GreaterThan)
        );

        return $recordset !== [];
    }

    /**
     * @param int $userId
     * @return string
     * @throws MinimalismException
     */
    public function generateCode(
        int $userId,
    ): string{
        $this->purgeExpired();

        $codes = $this->readByUserId($userId);

        if ($codes === []) {
            try {
                $response = random_int(100000, 999999);
            } catch (Exception) {
                /** @noinspection RandomApiMigrationInspection */
                $response = rand(100000, 999999);
            }

            $newCode = new Code(userId: $userId, code: $response);

            /** @noinspection UnusedFunctionResultInspection */
            $this->data->create(
                queryFactory: $newCode,
                responseType: Code::class,
            );
        } else {
            $response = $codes[0]->getCode();
        }

        return $response;
    }
}