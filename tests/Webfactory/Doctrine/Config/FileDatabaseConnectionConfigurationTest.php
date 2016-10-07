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

        $file = $configuration->getDatabaseFile();
        $this->assertInstanceOf('SplFileInfo', $file);
        $this->assertEquals($path, $file->getPathname());
    }

    public function testGeneratedFileNameIsNotChangedForExistingConfigurationObject()
    {
        $configuration = new FileDatabaseConnectionConfiguration();

        $firstCall = $configuration->getDatabaseFile();
        $secondCall = $configuration->getDatabaseFile();
        $this->assertInstanceOf('SplFileInfo', $firstCall);
        $this->assertInstanceOf('SplFileInfo', $secondCall);
        $this->assertEquals($firstCall->getPathname(), $secondCall->getPathname());
    }

    public function testGeneratesUniqueFileNameIfFilePathIsOmitted()
    {
        $firstConfiguration = new FileDatabaseConnectionConfiguration();
        $secondConfiguration = new FileDatabaseConnectionConfiguration();

        $firstFile = $firstConfiguration->getDatabaseFile();
        $secondFile = $secondConfiguration->getDatabaseFile();
        $this->assertInstanceOf('SplFileInfo', $firstFile);
        $this->assertInstanceOf('SplFileInfo', $secondFile);
        $this->assertNotEquals($firstFile->getPathname(), $secondFile->getPathname());
    }

    public function testCleanUpRemovesTheDatabaseFileIfItExists()
    {
        $configuration = new FileDatabaseConnectionConfiguration();
        $file = $configuration->getDatabaseFile();
        $this->assertInstanceOf('SplFileInfo', $file);
        touch($file->getPathname());

        $configuration->cleanUp();

        $this->assertFileNotExists($file->getPathname());
    }

    public function testCleanUpDoesNothingIfTheDatabaseFileDoesNotExistYet()
    {
        $configuration = new FileDatabaseConnectionConfiguration();
        $file = $configuration->getDatabaseFile();
        $this->assertInstanceOf('SplFileInfo', $file);

        $this->assertFileNotExists($file->getPathname());

        $this->setExpectedException(null);
        $configuration->cleanUp();
    }

    public function testCleanUpProvidesFluentInterface()
    {
        $configuration = new FileDatabaseConnectionConfiguration();

        $this->assertSame($configuration, $configuration->cleanUp());
    }

    /**
     * Checks if the connection configuration *really* works with the infrastructure.
     */
    public function testWorksWithInfrastructure()
    {
        $configuration = new FileDatabaseConnectionConfiguration();
        $infrastructure = $this->createInfrastructure($configuration);

        $this->setExpectedException(null);
        $infrastructure->import(new TestEntity());
    }

    public function testDatabaseFileIsCreated()
    {
        $configuration = new FileDatabaseConnectionConfiguration();

        $infrastructure = $this->createInfrastructure($configuration);
        $infrastructure->import(new TestEntity());

        $file = $configuration->getDatabaseFile();
        $this->assertInstanceOf('SplFileInfo', $file);
        $this->assertFileExists($file->getPathname());
    }

    /**
     * Creates a new infrastructure with the given connection configuration.
     *
     * @param ConnectionConfiguration $configuration
     * @return ORMInfrastructure
     */
    private function createInfrastructure(ConnectionConfiguration $configuration)
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(
            array(
                'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
            ),
            $configuration
        );
        return $infrastructure;
    }
}
