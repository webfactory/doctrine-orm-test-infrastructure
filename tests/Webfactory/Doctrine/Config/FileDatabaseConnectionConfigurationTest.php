<?php

namespace Webfactory\Doctrine\Config;

use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;

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

    /**
     * Checks if the connection configuration *really* works with the infrastructure.
     */
    public function testWorksWithInfrastructure()
    {
        $configuration = new FileDatabaseConnectionConfiguration();
        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ), $configuration);

        $this->setExpectedException(null);
        $infrastructure->import(new TestEntity());
    }

    public function testDatabaseIsRemovedWhenInfrastructureIsDestroyed()
    {
        $configuration = new FileDatabaseConnectionConfiguration();
        $databaseFile  = $configuration->getDatabaseFile();

        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ), $configuration);
        $infrastructure->import(new TestEntity());

        $configuration = null;
        $infrastructure = null;
        $this->assertFileNotExists($databaseFile);
    }
}
