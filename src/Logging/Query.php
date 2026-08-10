<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Logging;

/**
 * Represents a query that has been executed.
 */
class Query
{
    public function __construct(
        protected readonly string $sql,
        protected readonly array $params,
        protected readonly float $executionTimeInSeconds,
    ) {
    }

    /**
     * Returns the SQL of the query.
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Returns a list of parameters that have been assigned to the statement.
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Returns the execution time of the query in seconds.
     */
    public function getExecutionTimeInSeconds(): float
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
