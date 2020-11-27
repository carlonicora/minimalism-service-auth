<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;

class AppleIdsTable extends AbstractTable
{
    /** @var string */
    protected string $tableName = 'appleIds';

    /** @var array  */
    protected array $fields = [
        'appleId'   => FieldInterface::STRING
                    +  FieldInterface::PRIMARY_KEY,
        'userId'    => FieldInterface::INTEGER
    ];

    /**
     * @param string $appleId
     * @return array
     * @throws DbRecordNotFoundException
     * @throws DbSqlException
     */
    public function loadByAppleId(string $appleId): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE appleId=?';
        $this->parameters = ['s', $appleId];

        return $this->functions->runReadSingle();
    }
}