<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\Config;

use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\Config\ConnectionConfiguration;
use Webfactory\Doctrine\Config\FileDatabaseConnectionConfiguration;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\TestEntity;

class FileDatabaseConnectionConfigurationTest extends TestCase
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

        $this->assertNull(
            $infrastructure->import(new TestEntity())
        );
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
                TestEntity::class
            ),
            $configuration
        );

        return $infrastructure;
    }
}
