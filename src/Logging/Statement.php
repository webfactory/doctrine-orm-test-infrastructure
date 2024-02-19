<?php

namespace Webfactory\Doctrine\Logging;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;

class Statement extends AbstractStatementMiddleware
{
    private array $params = [];

    public function __construct(
        StatementInterface $wrappedStatement,
        private readonly QueryCollection $queries,
        private readonly string $sql
    ) {
        parent::__construct($wrappedStatement);
    }

    public function bindValue($param, $value, $type = ParameterType::STRING): void
    {
        $this->params[$param] = $value;

        parent::bindValue($param, $value, $type);
    }

    public function execute($params = []): Result
    {
        $start = microtime(true);
        try {
            return parent::execute();
        } finally {
            $this->queries->addQuery(new Query($this->sql, $this->params, microtime(true) - $start));
        }
    }
}