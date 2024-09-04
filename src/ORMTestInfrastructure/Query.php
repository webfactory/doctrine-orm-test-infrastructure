<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Represents a query that has been executed.
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
     * Currently not used:
     * - types
     *
     * @param string $sql - sql
     * @param mixed[] $params - params
     */
    public function __construct($sql, array $params)
    {
        $this->sql = $sql;
        $this->params = $params;
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
