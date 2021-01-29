<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class AppleIdsTable extends AbstractMySqlTable
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
     * @throws Exception
     */
    public function loadByAppleId(string $appleId): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE appleId=?';
        $this->parameters = ['s', $appleId];

        return $this->functions->runRead();
    }
}