<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class AuthsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'auths';

    /** @var array  */
    protected static array $fields = [
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
     * @throws Exception
     */
    public function loadByCode(string $code): array
    {
        $this->sql = $this->query->SELECT()
            . ' WHERE code=?;';
        $this->parameters = ['s', $code];

        return $this->functions->runRead();
    }
}