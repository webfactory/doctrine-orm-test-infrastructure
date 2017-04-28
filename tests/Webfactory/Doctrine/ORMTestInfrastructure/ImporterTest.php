<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Tests the importer.
 */
class ImporterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * System under test.
     *
     * @var \Webfactory\Doctrine\ORMTestInfrastructure\Importer
     */
    protected $importer = null;

    /**
     * The (mocked) entity manager.
     *
     * @var \Doctrine\ORM\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->entityManager = $this->createEntityManager();
        $this->importer      = new Importer($this->entityManager);
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->importer      = null;
        $this->entityManager = null;
        parent::tearDown();
    }

    /**
     * Checks if import() passes an object manager to a provided callback.
     *
     * Only an object manager instance, not the original entity manager is
     * expected as the importer may decide to uses a decorator.
     */
    public function testImportPassesObjectManagerToCallback()
    {
        $callable = $this->getMock('\stdClass', array('__invoke'));
        $callable->expects($this->once())
                 ->method('__invoke')
                 ->with($this->isInstanceOf(ObjectManager::class));

        $this->importer->import($callable);
    }

    /**
     * Checks if persist() calls from a callable are delegated to the entity manager.
     */
    public function testEntitiesFromCallableArePersisted()
    {
        $callable = function (ObjectManager $objectManager) {
            $objectManager->persist(new \stdClass());
            $objectManager->persist(new \stdClass());
        };

        $this->entityManager->expects($this->exactly(2))
                            ->method('persist')
                            ->with($this->isInstanceOf(\stdClass::class));

        $this->importer->import($callable);
    }

    /**
     * Checks if the importer accepts a file to persist entities.
     */
    public function testImportAddsEntitiesFromFile()
    {
        $this->entityManager->expects($this->exactly(2))
                            ->method('persist')
                            ->with($this->isInstanceOf(\stdClass::class));

        $path = __DIR__ . '/_files/Importer/LoadEntities.php';
        $this->importer->import($path);
    }

    /**
     * Ensures that entities, which are returned by a file, are persisted by the importer.
     */
    public function testImportAddsEntitiesThatAreReturnedFromFile()
    {
        $this->entityManager->expects($this->exactly(2))
                            ->method('persist')
                            ->with($this->isInstanceOf(\stdClass::class));

        $path = __DIR__ . '/_files/Importer/ReturnEntities.php';
        $this->importer->import($path);
    }

    /**
     * Checks if import() persists a single entity.
     */
    public function testImportPersistsSingleEntity()
    {
        $this->entityManager->expects($this->once())
                            ->method('persist')
                            ->with($this->isInstanceOf(\stdClass::class));

        $this->importer->import(new \stdClass());
    }

    /**
     * Ensures that import persists an array of entities.
     */
    public function testImportPersistsArrayOfEntities()
    {
        $this->entityManager->expects($this->exactly(2))
                            ->method('persist')
                            ->with($this->isInstanceOf(\stdClass::class));

        $entities = array(
            new \stdClass(),
            new \stdClass()
        );
        $this->importer->import($entities);
    }

    /**
     * Ensures that import() throws an exception if the given data source
     * is not supported.
     */
    public function testImportThrowsExceptionIfDataSourceIsNotSupported()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->importer->import(42);
    }

    /**
     * Checks if the importer detaches the provided entities.
     */
    public function testImporterDetachesEntities()
    {
        $this->entityManager->expects($this->exactly(2))
                            ->method('detach')
                            ->with($this->isInstanceOf(\stdClass::class));

        $entities = array(
            new \stdClass(),
            new \stdClass()
        );
        $this->importer->import($entities);
    }

    public function testEntityManagerIsFlushedOnlyOnce()
    {
        $this->entityManager->expects($this->once())
            ->method('flush');

        $entities = array(
            new \stdClass()
        );
        $this->importer->import($entities);
    }

    public function testEntitiesAreDetachedAfterFlush()
    {
        $detached = 0.0;
        $this->entityManager->expects($this->atLeastOnce())
            ->method('detach')
            ->will($this->returnCallback(function () use (&$detached) {
                $detached = microtime(true);
            }));
        $flushed = 0.0;
        $this->entityManager->expects($this->once())
            ->method('flush')
            ->will($this->returnCallback(function () use (&$flushed) {
                $flushed = microtime(true);
            }));

        $entities = array(
            new \stdClass()
        );
        $this->importer->import($entities);

        $this->assertGreaterThan($flushed, $detached, 'detach() was called before flush().');
    }

    /**
     * Creates a mocked entity manager.
     *
     * @return \Doctrine\ORM\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEntityManager()
    {
        $mock = $this->getMock(EntityManagerInterface::class);
        // Simulates the transactional() call on the entity manager.
        $transactional = function ($callback) use ($mock) {
            /* @var $mock \Doctrine\ORM\EntityManagerInterface */
            $result = call_user_func($callback, $mock);
            $mock->flush();
            return $result;
        };
        $mock->expects($this->any())
             ->method('transactional')
             ->will($this->returnCallback($transactional));
        return $mock;
    }
}
