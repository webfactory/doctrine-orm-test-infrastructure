<?php

namespace Webfactory\Doctrine\Logging;

use Doctrine\DBAL\Driver as DriverInterface;

class Middleware implements DriverInterface\Middleware
{
    private readonly QueryCollection $queries;

    public function __construct()
    {
        $this->queries = new QueryCollection();
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver($driver, $this->queries);
    }

    /**
     * @return Query[]
     */
    public function getQueries(): array
    {
        return $this->queries->queries;
    }

    public function isEnabled(): bool
    {
        return $this->queries->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->queries->enabled = $enabled;
    }
}