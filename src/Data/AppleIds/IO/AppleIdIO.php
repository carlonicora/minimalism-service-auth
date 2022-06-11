<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\AppleIds\IO;

use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\Databases\AppleIdsTable;
use CarloNicora\Minimalism\Services\Auth\Data\AppleIds\DataObjects\AppleId;
use Exception;

class AppleIdIO extends AbstractSqlIO
{
    /**
     * @param string $appleId
     * @return array
     * @throws Exception
     */
    public function readByAppleId(
        string $appleId,
    ): array
    {
        $factory = SqlQueryFactory::create(AppleIdsTable::class)
            ->addParameter(field: AppleIdsTable::appleId, value: $appleId);

        return $this->data->read(
            queryFactory: $factory,
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
            responseType: AppleId::class,
        );
    }
}