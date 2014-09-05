<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;

/**
 * Tests the infrastructure.
 */
class ORMInfrastructureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * System under test.
     *
     * @var \Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure
     */
    protected $infrastructure = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->infrastructure = new ORMInfrastructure(array(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ));
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->infrastructure = null;
        parent::tearDown();
    }

    /**
     * Checks if getEntityManager() returns the Doctrine entity manager,
     */
    public function testGetEntityManagerReturnsDoctrineEntityManager()
    {
        $entityManager = $this->infrastructure->getEntityManager();

        $this->assertInstanceOf('Doctrine\ORM\EntityManager', $entityManager);
    }

    /**
     * Ensures that getRepository() returns the Doctrine repository that belongs
     * to the given entity class.
     */
    public function testGetRepositoryReturnsRepositoryThatBelongsToEntity()
    {
        $repository = $this->infrastructure->getRepository(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );

        $this->assertInstanceOf(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityRepository',
            $repository
        );
    }

    /**
     * Checks if import() adds entities to the database.
     *
     * There are different options to import entities, but these are handled in detail
     * in the importer tests.
     *
     * @see \Webfactory\Doctrine\ORMTestInfrastructure\ImporterTest
     */
    public function testImportAddsEntities()
    {
        $entity = new TestEntity();
        $entity->name = 'unit-test';
        $repository = $this->infrastructure->getRepository(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );

        $entities = $repository->findAll();
        $this->assertCount(0, $entities);

        $this->infrastructure->import($entity);

        $entities = $repository->findAll();
        $this->assertCount(1, $entities);
    }

    /**
     * Checks if an imported entity receives a generated ID.
     */
    public function testEntityIdIsAvailableAfterImport()
    {
        $entity = new TestEntity();
        $entity->name = 'unit-test';

        $this->infrastructure->import($entity);

        $this->assertNotNull($entity->id);
    }

    /**
     * Ensures that imported entities are really loaded from the database and
     * not provided from identity map.
     */
    public function testImportedEntitiesAreReloadedFromDatabase()
    {
        $entity = new TestEntity();
        $entity->name = 'unit-test';
        $repository = $this->infrastructure->getRepository(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );

        $this->infrastructure->import($entity);

        $loadedEntity = $repository->find($entity->id);
        $this->assertInstanceOf(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity',
            $loadedEntity
        );
        $this->assertNotSame($entity, $loadedEntity);
    }

    /**
     * Ensures that entities with non-Doctrine annotations can be used.
     */
    public function testInfrastructureCanUseEntitiesWithNonDoctrineAnnotations()
    {
        $infrastructure = new ORMInfrastructure(array(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\AnnotatedTestEntity'
        ));

        $this->setExpectedException(null);
        $infrastructure->getEntityManager();
    }

    /**
     * Ensures that different infrastructure instances provide database isolation.
     */
    public function testDifferentInfrastructureInstancesUseSeparatedDatabases()
    {
        $entity = new TestEntity();
        $entity->name = 'unit-test';
        $anotherInfrastructure = new ORMInfrastructure(array(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ));
        $repository = $anotherInfrastructure->getRepository(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );

        $this->infrastructure->import($entity);

        // Entity must not be visible in the scope of another infrastructure.
        $entities = $repository->findAll();
        $this->assertCount(0, $entities);
    }

}
 