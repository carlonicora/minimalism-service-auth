<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;

class TokensTable extends AbstractTable
{
    /** @var string */
    protected string $tableName = 'tokens';

    /** @var array  */
    protected array $fields = [
        'tokenId'   => FieldInterface::INTEGER
                    +  FieldInterface::PRIMARY_KEY
                    +  FieldInterface::AUTO_INCREMENT,
        'appId'     => FieldInterface::INTEGER,
        'userId'    => FieldInterface::INTEGER,
        'isUser'    => FieldInterface::INTEGER,
        'token'     => FieldInterface::STRING
    ];

    /**
     * @param string $token
     * @return array
     * @throws DbSqlException|DbRecordNotFoundException
     */
    public function loadByToken(string $token): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE token=?;';
        $this->parameters = ['s', $token];

        return $this->functions->runReadSingle();
    }
}