<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class QueryLogger extends AbstractLogger
{
    public bool $enabled = true;
    private array $queries = [];

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        if (!$this->enabled) {
            return;
        }

        if (str_starts_with($message, 'Executing')) {
            $this->queries[] = new Query($context['sql'], $context['params'] ?? []);
        } else if ('Beginning transaction' === $message) {
            $this->queries[] = new Query('"START TRANSACTION"', []);
        } else if ('Committing transaction' === $message) {
            $this->queries[] = new Query('"COMMIT"', []);
        } else if ('Rolling back transaction' === $message) {
            $this->queries[] = new Query('"ROLLBACK"', []);
        }
    }

    public function getQueries()
    {
        return $this->queries;
    }
}
