<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class TokensTable extends AbstractMySqlTable
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
     * @throws Exception|DbRecordNotFoundException
     */
    public function loadByToken(string $token): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE token=?;';
        $this->parameters = ['s', $token];

        return $this->functions->runReadSingle();
    }
}