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
     * Returns the SQL of the query.
     *
     * @return string
     */
    public function getSql()
    {

    }

    /**
     * Returns a list of parameters that have been assigned to the statement.
     *
     * @return mixed[]
     */
    public function getParams()
    {

    }

    /**
     * Returns the execution time of the query in seconds.
     *
     * @return double
     */
    public function getExecutionTimeInSeconds()
    {

    }

    /**
     * Returns a string representation of the query and its params.
     *
     * @return string
     */
    public function __toString()
    {

    }
}
