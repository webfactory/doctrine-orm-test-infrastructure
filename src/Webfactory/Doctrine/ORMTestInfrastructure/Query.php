<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Represents a query that has been executed.
 *
 * This class is designed to be populated by the data that is gathered by the DebugStack logger.
 *
 * @see \Doctrine\DBAL\Logging\DebugStack
 */
class Query
{
    // ('sql' => $sql, 'params' => $params, 'types' => $types, 'executionMS' => 0
    public function getSql()
    {

    }

    public function getParams()
    {

    }

    public function getExecutionTimeInSeconds()
    {

    }
}
