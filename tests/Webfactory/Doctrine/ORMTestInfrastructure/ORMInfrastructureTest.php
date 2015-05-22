<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityWithDependency;

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
    public function testGetRepositoryReturnsRepositoryThatBelongsToEntityClass()
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
     * Ensures that getRepository() returns the Doctrine repository that belongs
     * to the given entity object.
     */
    public function testGetRepositoryReturnsRepositoryThatBelongsToEntityObject()
    {
        $entity = new TestEntity();
        $repository = $this->infrastructure->getRepository($entity);

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
        $repository = $this->infrastructure->getRepository($entity);

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
        $repository = $this->infrastructure->getRepository($entity);

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
        $anotherInfrastructure = new ORMInfrastructure(array(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ));
        $repository = $anotherInfrastructure->getRepository($entity);

        $this->infrastructure->import($entity);

        // Entity must not be visible in the scope of another infrastructure.
        $entities = $repository->findAll();
        $this->assertCount(0, $entities);
    }

    /**
     * Ensures that the query list that is provided by getQueries() is initially empty.
     */
    public function testGetQueriesReturnsInitiallyEmptyList()
    {
        $queries = $this->infrastructure->getQueries();

        $this->assertInternalType('array', $queries);
        $this->assertCount(0, $queries);
    }

    /**
     * Ensures that getQueries() returns the logged SQL queries as objects.
     */
    public function testGetQueriesReturnsQueryObjects()
    {
        $entity = new TestEntity();
        $repository = $this->infrastructure->getRepository($entity);
        $repository->find(42);

        $queries = $this->infrastructure->getQueries();

        $this->assertInternalType('array', $queries);
        $this->assertContainsOnly('\Webfactory\Doctrine\ORMTestInfrastructure\Query', $queries);
    }

    /**
     * Checks if the queries that are executed with the entity manager are logged.
     */
    public function testInfrastructureLogsExecutedQueries()
    {
        $entity = new TestEntity();
        $repository = $this->infrastructure->getRepository($entity);
        $repository->find(42);

        $queries = $this->infrastructure->getQueries();

        $this->assertInternalType('array', $queries);
        $this->assertCount(1, $queries);
    }

    /**
     * Ensures that the queries that are issued during data import are not logged.
     */
    public function testInfrastructureDoesNotLogImportQueries()
    {
        $entity = new TestEntity();
        $this->infrastructure->import($entity);

        $queries = $this->infrastructure->getQueries();

        $this->assertInternalType('array', $queries);
        $this->assertCount(0, $queries);
    }

    /**
     * Ensures that the infrastructure logs queries, which are executed after an import.
     */
    public function testInfrastructureLogsQueriesThatAreExecutedAfterImport()
    {
        $entity = new TestEntity();
        $this->infrastructure->import($entity);
        $repository = $this->infrastructure->getRepository($entity);
        $repository->find(42);

        $queries = $this->infrastructure->getQueries();

        $this->assertInternalType('array', $queries);
        $this->assertCount(1, $queries);
    }

    /**
     * Ensures that referenced sub-entities are automatically prepared if the infrastructure is
     * requested to handle such cases.
     */
    public function testInfrastructureAutomaticallyPerformsDependencySetupIfRequested()
    {
        $infrastructure = new ORMInfrastructure(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityWithDependency'
        ), true);

        $entityWithDependency = new TestEntityWithDependency();

        // Saving without prepared sub-entity would fail.
        $this->setExpectedException(null);
        $infrastructure->getEntityManager()->persist($entityWithDependency);
        $infrastructure->getEntityManager()->flush();
    }

    /**
     * Checks if the automatic dependency setup can cope with reference cycles,
     * for example if an entity references itself.
     */
    public function testAutomaticDependencyDetectionCanHandleCycles()
    {

    }

    /**
     * Checks if the automatic dependency setup can cope with chained references.
     *
     * Example:
     *
     *     A -> B -> C
     *
     * A references B, B references C. A is not directly related to C.
     */
    public function testAutomaticDependencyDetectionCanHandleChainedRelations()
    {

    }
}
