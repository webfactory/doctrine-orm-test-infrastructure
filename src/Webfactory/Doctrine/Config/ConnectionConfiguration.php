<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Config;

/**
 * Represents a Doctrine database connection configuration.
 *
 * This class has been created to be able to use type hints for connection parameters
 * and to be able to provide pre-configured connection configurations (for example as
 * subclasses or via factory).
 *
 * Any connection parameters that are supported by Doctrine DBAL can be used in the configuration.
 *
 * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
 */
class ConnectionConfiguration
{
    /**
     * @param array<string, mixed> $connectionParameters
     */
    public function __construct(array $connectionParameters)
    {

    }

    /**
     * Returns the connection parameters that are passed to Doctrine.
     *
     * @return array<string, mixed>
     */
    public function getConnectionParameters()
    {

    }
}
