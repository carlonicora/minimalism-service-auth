<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\AppleIds\IO;

use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\Databases\AppleIdsTable;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\DataObjects\AppleId;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use Exception;

class AppleIdIO extends AbstractSqlIO
{
    /**
     * @param string $appleId
     * @return AppleId
     * @throws Exception
     */
    public function readByAppleId(
        string $appleId,
    ): AppleId
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(AppleIdsTable::class)
                ->addParameter(AppleIdsTable::appleId, $appleId),
            responseType: AppleId::class,
        );
    }

    /**
     * @param AppleId $appleId
     * @return array
     */
    public function insert(
        AppleId $appleId,
    ): array
    {
        return $this->data->create(
            queryFactory: $appleId,
        );
    }
}