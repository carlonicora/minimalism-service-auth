<?php
namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractTable;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;

class ScopesTable extends AbstractTable {
    /** @var string */
    protected string $tableName = 'scopes';

    /** @var array  */
    protected array $fields = [
        'scopeId'   => TableInterface::INTEGER
                    +  TableInterface::PRIMARY_KEY
                    +  TableInterface::AUTO_INCREMENT,
        'name'      => TableInterface::STRING
    ];

    /**
     * @param int $appId
     * @return array
     * @throws DbSqlException
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