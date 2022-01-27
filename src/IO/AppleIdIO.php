<?php
namespace CarloNicora\Minimalism\Services\Auth\IO;

use CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\AppleIdsTable;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use Exception;

class AppleIdIO extends AbstractLoader
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
        /** @see AppleIdsTable::readByAppleId() */
        $recordset = $this->data->read(
            tableInterfaceClassName: AppleIdsTable::class,
            functionName: 'readByAppleId',
            parameters: [$appleId],
        );

        return $this->returnSingleValue(
            recordset: $recordset,
        );
    }

    /**
     * @param string $appleId
     * @param int $userId
     * @return array
     */
    public function insert(
        string $appleId,
        int $userId,
    ): array
    {
        return $this->data->insert(
            tableInterfaceClassName: AppleIdsTable::class,
            records: [
                'appleId' => $appleId,
                'userId' => $userId,
            ]
        );
    }
}