<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\Config\ConnectionConfiguration;
use Webfactory\Doctrine\ORMTestInfrastructure\_files\ORMInfrastructure\Entity\DependencyResolverFixtures;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\AnnotatedTestEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Annotation\AnnotationForTestWithDependencyDiscovery;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\AnnotatedTestEntityForDependencyDiscovery;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Cascade\CascadePersistingEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Cascade\CascadePersistedEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ChainReferenceEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\EntityImplementation;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\InterfaceAssociation\EntityInterface;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\InterfaceAssociation\EntityWithAssociationAgainstInterface;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferenceCycleEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityRepository;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityWithDependency;

/**
 * Tests the infrastructure.
 */
class ORMInfrastructureTest extends TestCase
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
    protected function setUp(): void
    {
        parent::setUp();
        $this->infrastructure = new ORMInfrastructure(array(
            TestEntity::class
        ));
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown(): void
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
        $this->expectException(\InvalidArgumentException::class);
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

        $this->assertIsArray($queries);
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

        $this->assertIsArray($queries);
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

        $this->assertIsArray($queries);
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

        $this->assertIsArray($queries);
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

        $this->assertIsArray($queries);
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
        $infrastructure->getEntityManager()->persist($entityWithDependency);
        $this->assertNull(
            $infrastructure->getEntityManager()->flush()
        );
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
        $infrastructure->getEntityManager()->persist($entityWithCycle);
        $this->assertNull(
            $infrastructure->getEntityManager()->flush()
        );
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
        $infrastructure->getEntityManager()->persist($entityWithReferenceChain);
        $this->assertNull(
            $infrastructure->getEntityManager()->flush()
        );
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
     * Ensures that entities with non-Doctrine annotations can be used.
     */
    public function testInfrastructureCanUseEntitiesWithNonDoctrineAnnotations()
    {
        $infrastructure = ORMInfrastructure::createOnlyFor(array(
            AnnotatedTestEntity::class
        ));

        $this->assertInstanceOf(
            EntityManager::class,
            $infrastructure->getEntityManager()
        );
    }

    public function testGetEventManagerReturnsEventManager()
    {
        $this->assertInstanceOf(EventManager::class, $this->infrastructure->getEventManager());
    }

    public function testGetEventManagerReturnsSameEventManagerThatIsUsedByEntityManager()
    {
        $this->assertSame(
            $this->infrastructure->getEventManager(),
            $this->infrastructure->getEntityManager()->getEventManager()
        );
    }

    public function testCanHandleInterfaceAssociationsIfMappingIsProvided()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(EntityWithAssociationAgainstInterface::class);

        $infrastructure->registerEntityMapping(EntityInterface::class, EntityImplementation::class);

        $this->assertInstanceOf(
            EntityManager::class,
            $infrastructure->getEntityManager()
        );
    }

    public function testCannotRegisterEntityMappingAfterEntityManagerCreation()
    {
        $this->infrastructure->getEntityManager();

        $this->expectException(\LogicException::class);
        $this->assertNull(
            $this->infrastructure->registerEntityMapping(EntityInterface::class, EntityImplementation::class)
        );
    }

    /**
     * Checks if it is possible to pass a more specific connection configuration.
     */
    public function testUsesMoreSpecificConnectionConfiguration()
    {
        $this->infrastructure = new ORMInfrastructure(array(
            'Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ), new ConnectionConfiguration(array(
            'invalid' => 'configuration'
        )));

        // The passed configuration is simply invalid, therefore, we expect an exception.
        $this->expectException('Exception');
        $this->infrastructure->getEntityManager();
    }

    /**
     * @see https://github.com/webfactory/doctrine-orm-test-infrastructure/issues/23
     */
    public function testWorksWithCascadePersist()
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor(CascadePersistingEntity::class);
        $cascadingPersistingEntity = new CascadePersistingEntity();
        $cascadingPersistingEntity->add(new CascadePersistedEntity());
        $infrastructure->import($cascadingPersistingEntity);

        // If this call fails, then there are leftovers in the identity map.
        $this->assertNull(
            $infrastructure->getEntityManager()->flush()
        );
    }

    /**
     * @dataProvider resolverFixtures
     */
    public function testSchemaResults(array $classes, callable $validator): void
    {
        $infrastructure = ORMInfrastructure::createWithDependenciesFor($classes);
        $entityManager = $infrastructure->getEntityManager();
        $schemaTool = new SchemaTool($entityManager);

        $validator($schemaTool->getSchemaFromMetadata($infrastructure->getMetadataForSupportedEntities()));
    }

    public function resolverFixtures()
    {
        yield 'single entity' => [
            [DependencyResolverFixtures\SingleEntity\Entity::class],
            function (Schema $schema) {
                self::assertCount(1, $schema->getTableNames());
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldA'));
            },
        ];

        yield 'simple entity hierarchy' => [
            [DependencyResolverFixtures\TwoEntitiesInheritance\Entity::class],
            function (Schema $schema) {
                self::assertCount(1, $schema->getTableNames());
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldA'));
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldB'));
            },
        ];

        yield 'entity with mapped superclass as base class' => [
            [DependencyResolverFixtures\MappedSuperclassInheritance\Entity::class],
            function (Schema $schema) {
                self::assertCount(1, $schema->getTableNames());
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldA'));
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldB'));
            },
        ];

        yield 'fields from transient base class are present, but class is otherwise ignored' => [
            [DependencyResolverFixtures\TransientBaseClass\Entity::class],
            function (Schema $schema) {
                self::assertCount(1, $schema->getTableNames());
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldA'));
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldB'));
            },
        ];

        yield 'with single table inheritance, the table for the base class is present with all fields' => [
            [DependencyResolverFixtures\SingleTableInheritance\Entity::class],
            function (Schema $schema) {
                self::assertCount(1, $schema->getTableNames());
                self::assertTrue($schema->getTable('BaseEntity')->hasColumn('fieldA'));
                self::assertTrue($schema->getTable('BaseEntity')->hasColumn('fieldB'));
            },
        ];

        yield 'with joined table inheritance, tables for the base and subclass are present with all fields' => [
            [DependencyResolverFixtures\JoinedTableInheritance\Entity::class],
            function (Schema $schema) {
                self::assertCount(2, $schema->getTableNames());

                self::assertCount(3, $schema->getTable('BaseEntity')->getColumns()); // id, class, fieldA
                self::assertTrue($schema->getTable('BaseEntity')->hasColumn('fieldA'));

                self::assertCount(2, $schema->getTable('Entity')->getColumns()); // id-baseref, fieldB
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldB'));
            },
        ];

        yield 'with joined table inheritance, three tables are present along the class hierarchy' => [
            [DependencyResolverFixtures\JoinedTableInheritanceWithTwoLevels\Entity::class],
            function (Schema $schema) {
                self::assertCount(3, $schema->getTableNames());

                self::assertCount(3, $schema->getTable('BaseEntity')->getColumns()); // id, class, fieldA
                self::assertTrue($schema->getTable('BaseEntity')->hasColumn('fieldA'));

                self::assertCount(2, $schema->getTable('IntermediateEntity')->getColumns()); // id-baseref, fieldB
                self::assertTrue($schema->getTable('IntermediateEntity')->hasColumn('fieldB'));

                self::assertCount(2, $schema->getTable('Entity')->getColumns()); // id-baseref, fieldC
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldC'));
            },
        ];

        yield 'with joined table inheritance, all tables for the complete inheritance tree are present' => [
            [DependencyResolverFixtures\JoinedTableInheritanceWithTwoSubclasses\Entity::class],
            function (Schema $schema) {
                self::assertCount(3, $schema->getTableNames());

                self::assertCount(3, $schema->getTable('BaseEntity')->getColumns()); // id, class, fieldA
                self::assertTrue($schema->getTable('BaseEntity')->hasColumn('fieldA'));

                self::assertCount(2, $schema->getTable('SecondEntity')->getColumns()); // id-baseref, fieldB
                self::assertTrue($schema->getTable('SecondEntity')->hasColumn('fieldB'));

                self::assertCount(2, $schema->getTable('Entity')->getColumns()); // id-baseref, fieldC
                self::assertTrue($schema->getTable('Entity')->hasColumn('fieldC'));
            },
        ];

        // If it does not work as expected, this will even throw an exception because the table name is used more than once
        yield 'in a hierarchy, identical (=conflicting) table names are fine as long as only one class is used' => [
            [DependencyResolverFixtures\TwoEntitiesInheritanceWithConflictingTableNames\Entity::class],
            function (Schema $schema) {
                self::assertCount(1, $schema->getTableNames());
                self::assertTrue($schema->getTable('some_table')->hasColumn('fieldA'));
                self::assertTrue($schema->getTable('some_table')->hasColumn('fieldB'));
            },
        ];
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
