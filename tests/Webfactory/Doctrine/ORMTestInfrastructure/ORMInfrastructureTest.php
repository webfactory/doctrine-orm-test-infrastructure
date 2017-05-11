<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\AnnotatedTestEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Annotation\AnnotationForTestWithDependencyDiscovery;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\AnnotatedTestEntityForDependencyDiscovery;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ChainReferenceEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferenceCycleEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityRepository;
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
            TestEntity::class
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

        $this->assertInstanceOf(EntityManager::class, $entityManager);
    }

    /**
     * Ensures that getRepository() returns the Doctrine repository that belongs
     * to the given entity class.
     */
    public function testGetRepositoryReturnsRepositoryThatBelongsToEntityClass()
    {
        $repository = $this->infrastructure->getRepository(
            TestEntity::class
        );

        $this->assertInstanceOf(
            TestEntityRepository::class,
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
            TestEntityRepository::class,
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
            TestEntity::class,
            $loadedEntity
        );
        $this->assertNotSame($entity, $loadedEntity);
    }

    /**
     * Ensures that different infrastructure instances provide database isolation.
     */
    public function testDifferentInfrastructureInstancesUseSeparatedDatabases()
    {
        $entity = new TestEntity();
        $anotherInfrastructure = new ORMInfrastructure(array(
            TestEntity::class
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
        $this->assertContainsOnly(Query::class, $queries);
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
            TestEntity::class,
            ReferencedEntity::class
        ));

        $this->assertInstanceOf(ORMInfrastructure::class, $infrastructure);
    }

    /**
     * Ensures that createWithDependenciesFor() returns an infrastructure object if a single
     * entity class is provided.
     */
    public function testCreateWithDependenciesForCreatesInfrastructureForSingleEntity()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(
            TestEntity::class
        );

        $this->assertInstanceOf(ORMInfrastructure::class, $infrastructure);
    }

    /**
     * Ensures that createOnlyFor() returns an infrastructure object if a set of
     * entities classes is provided.
     */
    public function testCreateOnlyForCreatesInfrastructureForSetOfEntities()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            TestEntity::class,
            ReferencedEntity::class
        ));

        $this->assertInstanceOf(ORMInfrastructure::class, $infrastructure);
    }

    /**
     * Ensures that createOnlyFor() returns an infrastructure object if a single
     * entity class is provided.
     */
    public function testCreateOnlyForCreatesInfrastructureForSingleEntity()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(
            TestEntity::class
        );

        $this->assertInstanceOf(ORMInfrastructure::class, $infrastructure);
    }

    /**
     * Ensures that referenced sub-entities are automatically prepared if the infrastructure is
     * requested to handle such cases.
     */
    public function testInfrastructureAutomaticallyPerformsDependencySetupIfRequested()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(array(
            TestEntityWithDependency::class
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
            ReferenceCycleEntity::class
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
            ChainReferenceEntity::class
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
            TestEntity::class
        ));

        $metadata = $infrastructure->getEntityManager()->getMetadataFactory()->getAllMetadata();
        $entities = array_map(function (ClassMetadataInfo $info) {
            return ltrim($info->name, '\\');
        }, $metadata);
        $this->assertEquals(
            array(TestEntity::class),
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
            TestEntity::class
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
     * Ensures that entities with non-Doctrine annotations can be used.
     */
    public function testInfrastructureCanUseEntitiesWithNonDoctrineAnnotations()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            AnnotatedTestEntity::class
        ));

        $this->setExpectedException(null);
        $infrastructure->getEntityManager();
    }

    /**
     * This test covers a rare edge case.
     *
     * Prerequisites of the problem:
     *
     * - No custom annotation loader registered (e.g. if no infrastructure has been created yet)
     * - Infrastructure is created with dependency discovery
     * - Entity uses a custom annotation
     * - Annotation class has not been loaded yet
     *
     * Observation:
     *
     * - Exception stating that the annotation could not be loaded
     * - Creation of the infrastructure failed
     *
     * Reason:
     *
     * The dependency resolver scans the provided entities to find connected entities.
     * That happened early in the infrastructure constructor so that no annotation loader was registered yet.
     * Therefore, the annotation that is found cannot be loaded.
     *
     * @see \Webfactory\Doctrine\ORMTestInfrastructure\EntityDependencyResolver
     */
    public function testEntityDependencyDiscoveryWithCustomAnnotationThatWasNotLoadedBefore()
    {
        // Destruct the default infrastructure to ensure that its annotation loader is removed.
        $this->infrastructure = null;
        $this->assertEquals(
            0,
            $this->getNumberOfAnnotationLoaders(),
            'This test assumes that no custom annotation loaders are registered.'
        );
        $this->assertFalse(
            class_exists(AnnotationForTestWithDependencyDiscovery::class, false),
            sprintf(
                'This test assumes that the annotation class "%s" was not loaded before.',
                AnnotationForTestWithDependencyDiscovery::class
            )
        );

        $this->setExpectedException(null);
        ORMInfrastructure::createWithDependenciesFor(
            AnnotatedTestEntityForDependencyDiscovery::class
        );
    }

    /**
     * Returns the number of currently registered annotation loaders.
     *
     * @return integer
     */
    private function getNumberOfAnnotationLoaders()
    {
        $reflection = new \ReflectionClass(AnnotationRegistry::class);
        $annotationLoaderProperty = $reflection->getProperty('loaders');
        $annotationLoaderProperty->setAccessible(true);
        return count($annotationLoaderProperty->getValue());
    }
}
