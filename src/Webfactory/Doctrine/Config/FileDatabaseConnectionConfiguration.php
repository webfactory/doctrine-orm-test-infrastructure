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
        parent::__construct(array(
            'driver'   => 'pdo_sqlite',
            'user'     => 'root',
            'password' => '',
            'path'     => $this->toFilePath($filePath)
        ));
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
        $parameters = $this->getConnectionParameters();
        return $parameters['path'];
    }

    /**
     * Returns a file path for the database file.
     *
     * Generates a unique file name if the given $filePath is null.
     *
     * @param string|null $filePath
     * @return string
     */
    private function toFilePath($filePath)
    {
        if ($filePath === null) {
            return sys_get_temp_dir() . '/' . uniqid('db-', true) . '.sqlite';
        }
        return $filePath;
    }
}
