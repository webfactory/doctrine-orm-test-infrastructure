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
    /**
     * Currently not used:
     * - types
     *
     * @param string $sql - sql
     * @param mixed[] $params - params
     * @param double $executionTimeInSeconds - executionMS
     */
    public function __construct($sql, array $params, $executionTimeInSeconds)
    {

    }

    /**
     * @return string
     */
    public function getSql()
    {

    }

    /**
     * @return mixed[]
     */
    public function getParams()
    {

    }

    /**
     * @return double
     */
    public function getExecutionTimeInSeconds()
    {

    }
}
