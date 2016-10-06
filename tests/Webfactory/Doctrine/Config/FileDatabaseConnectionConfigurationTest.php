<?php

namespace Webfactory\Doctrine\Config;

class FileDatabaseConnectionConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testKeepsProvidedFilePath()
    {
        $path = __DIR__ . '/_files/my-db.sqlite';
        $configuration = new FileDatabaseConnectionConfiguration($path);

        $this->assertEquals($path, $configuration->getDatabaseFile());
    }

    public function testGeneratedFileNameIsNotChangedForExistingConfigurationObject()
    {
        $configuration = new FileDatabaseConnectionConfiguration();

        $this->assertEquals($configuration->getDatabaseFile(), $configuration->getDatabaseFile());
    }

    public function testGeneratesUniqueFileNameIfFilePathIsOmitted()
    {
        $firstConfiguration = new FileDatabaseConnectionConfiguration();
        $secondConfiguration = new FileDatabaseConnectionConfiguration();

        $this->assertNotEquals($firstConfiguration->getDatabaseFile(), $secondConfiguration->getDatabaseFile());
    }
}
