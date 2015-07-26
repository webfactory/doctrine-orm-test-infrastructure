<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Athletic\AthleticEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;

/**
 * Compares the different methods that can be used to import test data.
 */
class ImportEvent extends AthleticEvent
{
    /**
     * Infrastructure that is used to import entities.
     *
     * @var ORMInfrastructure
     */
    protected $infrastructure = null;

    /**
     * Prepares the environment before each iteration.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->infrastructure = ORMInfrastructure::createOnlyFor(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );
    }

    /**
     * Cleans up after each iteration.
     */
    protected function tearDown()
    {
        $this->infrastructure = null;
        parent::tearDown();
    }

    /**
     * Determines the time it takes to create 10 entities.
     *
     * You should take out the calculated time from the import benchmarks
     * (once per iteration) to get the raw import times.
     *
     * @iterations 200
     */
    public function creationOf10Entities()
    {
        $this->createEntities(10);
    }

    /**
     * Analog to measuring the creation time of 10 entities.
     *
     * @iterations 200
     * @see creationOf10Entities()
     */
    public function creationOf100Entities()
    {
        $this->createEntities(100);
    }

    /**
     * @iterations 200
     */
    public function import10EntitiesFromList()
    {
        $this->infrastructure->import($this->createEntities(10));
    }

    /**
     * @iterations 200
     */
    public function import100EntitiesFromList()
    {
        $this->infrastructure->import($this->createEntities(100));
    }

    /**
     * @iterations 200
     */
    public function import10EntitiesViaCallback()
    {
        $this->infrastructure->import(function (ObjectManager $objectManager) {
            for ($i = 0; $i < 10; $i++) {
                $objectManager->persist(new TestEntity());
            }
        });
    }

    /**
     * @iterations 200
     */
    public function import100EntitiesViaCallback()
    {
        $this->infrastructure->import(function (ObjectManager $objectManager) {
            for ($i = 0; $i < 100; $i++) {
                $objectManager->persist(new TestEntity());
            }
        });
    }

    /**
     * @iterations 200
     */
    public function import10EntitiesViaPhpFile()
    {
        $this->infrastructure->import(
            $this->getImportFilePath('import-10-entities-via-object-manager.php')
        );
    }

    /**
     * @iterations 200
     */
    public function import100EntitiesViaPhpFile()
    {
        $this->infrastructure->import(
            $this->getImportFilePath('import-100-entities-via-object-manager.php')
        );
    }

    /**
     * @iterations 200
     */
    public function import10EntitiesReturnedByPhpFile()
    {
        $this->infrastructure->import(
            $this->getImportFilePath('return-10-entities.php')
        );
    }

    /**
     * @iterations 200
     */
    public function import100EntitiesReturnedByPhpFile()
    {
        $this->infrastructure->import(
            $this->getImportFilePath('return-100-entities.php')
        );
    }

    /**
     * Creates the requested number of entities.
     *
     * @param integer $number
     * @return TestEntity[]
     */
    protected function createEntities($number)
    {
        $entities = array();
        for ($i = 0; $i < $number; $i++) {
            $entities[] = new TestEntity();
        }
        return $entities;
    }

    /**
     * Returns the path to the import file with the provided name.
     *
     * @param string $fileName
     * @return string
     */
    protected function getImportFilePath($fileName)
    {
        return __DIR__ . '/_files/' . $fileName;
    }
}
