<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class AuthsTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'auths';

    /** @var array  */
    protected array $fields = [
        'authId'        => FieldInterface::INTEGER
                        +  FieldInterface::PRIMARY_KEY
                        +  FieldInterface::AUTO_INCREMENT,
        'appId'         => FieldInterface::INTEGER,
        'userId'        => FieldInterface::INTEGER,
        'expiration'    => FieldInterface::STRING,
        'code'          => FieldInterface::STRING
    ];

    /**
     * @param string $code
     * @return array
     * @throws Exception|DbRecordNotFoundException
     */
    public function loadByCode(string $code): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE code=?;';
        $this->parameters = ['s', $code];

        return $this->functions->runReadSingle();
    }
}