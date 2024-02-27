<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Config;

/**
 * Specifies a connection to an in-memory SQLite database.
 */
class InMemoryDatabaseConnectionConfiguration extends ConnectionConfiguration
{
    /**
     * Creates a connection configuration that connects to an in-memory database.
     */
    public function __construct()
    {
        parent::__construct([
            'driver'   => 'pdo_sqlite',
            'memory'   => true
        ]);
    }
}
