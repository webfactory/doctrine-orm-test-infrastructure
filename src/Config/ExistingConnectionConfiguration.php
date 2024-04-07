<?php

namespace Webfactory\Doctrine\Config;

use Doctrine\DBAL\Connection;

/**
 * Allows to use a pre-existing Doctrine DBAL connection with the ORMInfrastructure
 */
class ExistingConnectionConfiguration extends ConnectionConfiguration
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct([]);

        $this->connection = $connection;
    }

    /**
     * Provides the existing DBAL connection
     *
     * This makes use of the fact that the first argument to EntityManager::create() is in fact
     * un-typed: You can pass in either a configuration array or an existing DBAL connection.
     *
     * @return Connection
     */
    public function getConnectionParameters()
    {
        return $this->connection;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
