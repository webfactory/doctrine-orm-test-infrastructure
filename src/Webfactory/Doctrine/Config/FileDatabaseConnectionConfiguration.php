<?php

namespace Webfactory\Doctrine\Config;

/**
 * Specifies a connection to a file-based SQLite database.
 */
class FileDatabaseConnectionConfiguration extends ConnectionConfiguration
{
    /**
     * @param string|null $filePath
     */
    public function __construct($filePath = null)
    {

    }

    /**
     * Returns the path to the database file.
     *
     * The database file may not exist.
     *
     * @return string
     */
    public function getDatabaseFile()
    {

    }
}
