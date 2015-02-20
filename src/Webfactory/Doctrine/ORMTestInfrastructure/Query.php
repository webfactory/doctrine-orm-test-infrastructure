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
     * The SQL query.
     *
     * @var string
     */
    protected $sql = null;

    /**
     * The assigned parameters.
     *
     * @var mixed[]
     */
    protected $params = null;

    /**
     * The execution time of the query in seconds.
     *
     * @var double
     */
    protected $executionTimeInSeconds = null;

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
        $this->sql = $sql;
        $this->params = $params;
        $this->executionTimeInSeconds = $executionTimeInSeconds;
    }

    /**
     * Returns the SQL of the query.
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Returns a list of parameters that have been assigned to the statement.
     *
     * @return mixed[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns the execution time of the query in seconds.
     *
     * @return double
     */
    public function getExecutionTimeInSeconds()
    {
        return $this->executionTimeInSeconds;
    }

    /**
     * Returns a string representation of the query and its params.
     *
     * @return string
     */
    public function __toString()
    {
        $template = '"%s" with parameters [%s]';
        return sprintf($template, $this->getSql(), implode(', ', $this->getParams()));
    }
}
