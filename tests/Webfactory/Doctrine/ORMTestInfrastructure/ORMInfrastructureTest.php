<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ChainReferenceEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferenceCycleEntity;
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
     * Ensure that the infrastructure fails fast if obviously invalid data is passed.
     */
    public function testInfrastructureRejectsNonClassNames()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        ORMInfrastructure::createOnlyFor(array('NotAClass'));
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
     * Ensures that createWithDependenciesFor() returns an infrastructure object if a set of
     * entities classes is provided.
     */
    public function testCreateWithDependenciesForCreatesInfrastructureForSetOfEntities()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity',
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity'
        ));

        $this->assertInstanceOf('\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure', $infrastructure);
    }

    /**
     * Ensures that createWithDependenciesFor() returns an infrastructure object if a single
     * entity class is provided.
     */
    public function testCreateWithDependenciesForCreatesInfrastructureForSingleEntity()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );

        $this->assertInstanceOf('\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure', $infrastructure);
    }

    /**
     * Ensures that createOnlyFor() returns an infrastructure object if a set of
     * entities classes is provided.
     */
    public function testCreateOnlyForCreatesInfrastructureForSetOfEntities()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity',
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity'
        ));

        $this->assertInstanceOf('\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure', $infrastructure);
    }

    /**
     * Ensures that createOnlyFor() returns an infrastructure object if a single
     * entity class is provided.
     */
    public function testCreateOnlyForCreatesInfrastructureForSingleEntity()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );

        $this->assertInstanceOf('\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure', $infrastructure);
    }

    /**
     * Ensures that referenced sub-entities are automatically prepared if the infrastructure is
     * requested to handle such cases.
     */
    public function testInfrastructureAutomaticallyPerformsDependencySetupIfRequested()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityWithDependency'
        ));

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
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferenceCycleEntity'
        ));

        $entityWithCycle = new ReferenceCycleEntity();

        // Saving will most probably work as no additional table is needed, but the reference
        // detection, which is performed before, might lead to an endless loop.
        $this->setExpectedException(null);
        $infrastructure->getEntityManager()->persist($entityWithCycle);
        $infrastructure->getEntityManager()->flush();
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
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ChainReferenceEntity'
        ));

        $entityWithReferenceChain = new ChainReferenceEntity();

        // All tables must be created properly, otherwise it is not possible to store the entity.
        $this->setExpectedException(null);
        $infrastructure->getEntityManager()->persist($entityWithReferenceChain);
        $infrastructure->getEntityManager()->flush();
    }

    /**
     * Ensures that it is not possible to retrieve the class names of entities,
     * which are not simulated by the infrastructure.
     *
     * If not handled properly, the metadata provides access to several entity classes.
     */
    public function testNotSimulatedEntitiesAreNotExposed()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ));

        $metadata = $infrastructure->getEntityManager()->getMetadataFactory()->getAllMetadata();
        $entities = array_map(function (ClassMetadataInfo $info) {
            return ltrim($info->name, '\\');
        }, $metadata);
        $this->assertEquals(
            array('Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'),
            $entities
        );
    }

    /**
     * This test checks if a ORMInfrastructure object is immediately destructed when external references are removed.
     *
     * This ensures that the cleanup process runs early and the test environment is not polluted with infrastructure
     * objects hanging in memory until the tests end.
     * This is not a perfect test as it relies on internal knowledge about the magic of infrastructure. However,
     * at the moment it is at least a viable solution.
     */
    public function testInfrastructureIsImmediatelyDestructed()
    {
        $beforeCreation = $this->getNumberOfAnnotationLoaders();
        $infrastructure = ORMInfrastructure::createOnlyFor(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        );
        $afterCreation = $this->getNumberOfAnnotationLoaders();
        $this->assertEquals(
            $beforeCreation + 1,
            $afterCreation,
            'This test assumes that each infrastructure add an annotation loader. ' .
            'It will not work if this prerequisite is not met.'
        );

        // Remove reference to the infrastructure object.
        unset($infrastructure);

        $afterDestruction = $this->getNumberOfAnnotationLoaders();
        $this->assertEquals(
            $beforeCreation,
            $afterDestruction,
            'Expected annotation loader to be immediately removed, which should happen in __destruct().'
        );
    }

    /**
     * Returns the number of currently registered annotation loaders.
     *
     * @return integer
     */
    private function getNumberOfAnnotationLoaders()
    {
        $reflection = new \ReflectionClass('\Doctrine\Common\Annotations\AnnotationRegistry');
        $annotationLoaderProperty = $reflection->getProperty('loaders');
        $annotationLoaderProperty->setAccessible(true);
        return count($annotationLoaderProperty->getValue());
    }
}
