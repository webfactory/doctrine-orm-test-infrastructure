<?php

namespace Webfactory\Doctrine\Logging;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;

class Connection extends AbstractConnectionMiddleware
{
    public function __construct(ConnectionInterface $wrappedConnection, private readonly QueryCollection $queries)
    {
        parent::__construct($wrappedConnection);
    }

    public function prepare(string $sql): StatementInterface
    {
        return new Statement(parent::prepare($sql), $this->queries, $sql);
    }

    public function exec(string $sql): int
    {
        $start = microtime(true);
        try {
            return parent::exec($sql);
        } finally {
            $this->queries->addQuery(new Query($sql, [], microtime(true) - $start));
        }
    }

    public function query(string $sql): Result
    {
        $start = microtime(true);
        try {
            return parent::query($sql);
        } finally {
            $this->queries->addQuery(new Query($sql, [], microtime(true) - $start));
        }
    }

    public function beginTransaction(): void
    {
        $start = microtime(true);
        try {
            parent::beginTransaction();
        } finally {
            $this->queries->addQuery(new Query('"BEGIN TRANSACTION"', [], microtime(true) - $start));
        }
    }

    public function commit(): void
    {
        $start = microtime(true);
        try {
            parent::commit();
        } finally {
            $this->queries->addQuery(new Query('"COMMIT"', [], microtime(true) - $start));
        }
    }

    public function rollBack(): void
    {
        $start = microtime(true);
        try {
            parent::rollBack();
        } finally {
            $this->queries->addQuery(new Query('"ROLLBACK"', [], microtime(true) - $start));
        }
    }
}