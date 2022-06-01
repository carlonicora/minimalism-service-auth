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
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->delete()
                ->addParameter(field: CodesTable::expirationTime, value: date('Y-m-d H:i:s'), comparison: SqlComparison::LesserThan)
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
     * @param string $email
     * @return Code[]
     * @throws MinimalismException
     */
    public function readByEmail(
        string $email,
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(field: CodesTable::email, value: $email),
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
     * @param string $email
     * @throws Exception
     */
    public function purgeEmail(
        string $email,
    ) : void
    {
        $this->data->delete(
            queryFactory: SqlQueryFactory::create(CodesTable::class)->delete()
                ->addParameter(field: CodesTable::email, value: $email)
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
     * @param string $email
     * @param int $code
     * @return array
     * @throws Exception
     */
    public function readByEmailCode(
        string $email,
        int $code,
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(CodesTable::class)
                ->addParameter(field: CodesTable::email, value: $email)
                ->addParameter(field: CodesTable::code, value: $code),
            responseType: Code::class,
            requireObjectsList: true,
        );
    }

    /**
     * @param int $code
     * @param int|null $userId
     * @param string|null $email
     * @return bool
     * @throws MinimalismException
     */
    public function isCodeValid(
        int $code,
        ?int $userId=null,
        ?string $email=null,
    ): bool
    {
        $this->purgeExpired();

        $factory = SqlQueryFactory::create(CodesTable::class)
            ->addParameter(field: CodesTable::code, value: $code)
            ->addParameter(field: CodesTable::expirationTime, value: date('Y-m-d H:i:s'), comparison: SqlComparison::GreaterThan);

        if ($userId !== null){
            $factory->addParameter(field: CodesTable::userId, value: $userId);
        } else {
            $factory->addParameter(field: CodesTable::email, value: $email);
        }

        $recordset = $this->data->read(
            queryFactory: $factory
        );

        return $recordset !== [];
    }

    /**
     * @param int|null $userId
     * @param string|null $email
     * @return string
     * @throws Exception
     */
    public function generateCode(
        ?int $userId=null,
        ?string $email=null,
    ): string{
        $this->purgeExpired();

        if ($userId !== null) {
            $codes = $this->readByUserId($userId);
        } else {
            $codes = $this->readByEmail($email);
        }

        if ($codes === []) {
            try {
                $response = random_int(100000, 999999);
            } catch (Exception) {
                /** @noinspection RandomApiMigrationInspection */
                $response = rand(100000, 999999);
            }

            $newCode = new Code();
            if ($userId !== null) {
                $newCode->setUserId($userId);
            } else {
                $newCode->setEmail($email);
            }
            $newCode->setCode($response);
            $newCode->setExpirationTime(time() + 60 * 5);

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