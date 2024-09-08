<?php

namespace Webfactory\Doctrine\Logging;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

class Driver extends AbstractDriverMiddleware
{
    public function __construct(DriverInterface $wrappedDriver, private readonly QueryCollection $queries)
    {
        parent::__construct($wrappedDriver);
    }

    public function connect(array $params): DriverConnection
    {
        return new Connection(parent::connect($params), $this->queries);
    }
}