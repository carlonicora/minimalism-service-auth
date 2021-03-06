<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class ScopesTable extends AbstractMySqlTable
{
    /** @var string */
    protected string $tableName = 'scopes';

    /** @var array  */
    protected array $fields = [
        'scopeId'   => FieldInterface::INTEGER
                    +  FieldInterface::PRIMARY_KEY
                    +  FieldInterface::AUTO_INCREMENT,
        'name'      => FieldInterface::STRING
    ];

    /**
     * @param int $appId
     * @return array
     * @throws Exception
     */
    public function getApplicationScopes(int $appId): array
    {
        $this->sql = $this->query->SELECT()
            . ' JOIN appScopes ON scopes.scopId=appScopes.scopeId'
            . ' WHERE appScopes.appId=?;';
        $this->parameters = ['i', $appId];

        return $this->functions->runRead();
    }
}