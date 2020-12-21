<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\EventManager;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\EventSubscriber;
use Webfactory\Doctrine\Config\ConnectionConfiguration;

/**
 * Helper class that creates the database infrastructure for a defined set of entity classes.
 *
 * The required database is created in memory (via SQLite). This provides full isolation
 * and allows testing repositories and entities against a real database.
 *
 * # Example #
 *
 * ## Setup ##
 *
 * Create the infrastructure for a set of entities:
 *
 *     $infrastructure = ORMInfrastructure::createOnlyFor(array(
 *        'My\Entity\ClassName'
 *     ));
 *
 * Use the infrastructure to retrieve the entity manager:
 *
 *     $entityManager = $infrastructure->getEntityManager();
 *
 * The entity manager can be used as usual. It operates on an in-memory database that contains
 * the schema for all entities that have been mentioned in the infrastructure constructor.
 *
 * ### Advanced Setup ###
 *
 * Use the ``createWithDependenciesFor()`` factory method to create an infrastructure for
 * the given entity, including all entities that are associated with it:
 *
 *     $infrastructure = ORMInfrastructure::createWithDependenciesFor(
 *        'My\Entity\ClassName'
 *     );
 *
 * This is convenient as it avoids touching tests when associations are added, but it
 * might also hide the existence of entity dependencies that you are not really aware
 * of.
 *
 * ## Import Test Data ##
 *
 * Additionally, the infrastructure provides means to import entities:
 *
 *     $myEntity = new \My\Entity\ClassName();
 *     $infrastructure->import($myEntity);
 *
 * The import ensures that the imported entities are loaded from the database when requested via repository. This
 * circumvents Doctrine's caching via identity map and thereby leads to a more realistic test environment.
 */
class ORMInfrastructure
{
    /**
     * The connection parameters that are used per default.
     *
     * Possible parameters are documented at
     * {@link http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html}.
     *
     * @var array(string=>mixed)
     * @deprecated To be removed in 2.0. Pass ConnectionConfiguration during construction to change the connection.
     */
    protected $defaultConnectionParams = array(
        'driver'   => 'pdo_sqlite',
        'user'     => 'root',
        'password' => '',
        'memory'   => true
    );

    /**
     * List of entity classes that are managed by this infrastructure.
     *
     * @var string[]
     */
    protected $entityClasses;

    /**
     * The entity manager that is used to perform entity operations.
     *
     * Contains null if the entity manager has not been created yet.
     *
     * @var \Doctrine\ORM\EntityManager|null
     */
    protected $entityManager = null;

    /**
     * The query logger that is used.
     *
     * @var DebugStack
     */
    protected $queryLogger = null;

    /**
     * The naming strategy that is used.
     *
     * @var NamingStrategy
     */
    protected $namingStrategy = null;

    /**
     * Callback that is used to load non-Doctrine annotations.
     *
     * @var \Closure
     */
    protected $annotationLoader = null;

    /**
     * Factory that is used to create ORM configurations.
     *
     * @var ConfigurationFactory
     * @deprecated To be removed in 2.0. Only used once and can be degraded to a local variable.
     */
    protected $configFactory = null;

    /**
     * Listener that is used to resolve entity mappings.
     *
     * Null if the listener is not registered yet.
     *
     * @var ResolveTargetEntityListener|null
     */
    private $resolveTargetListener;

    /**
     * The configuration that is used to connect to the test database.
     *
     * @var ConnectionConfiguration
     */
    private $connectionConfiguration = null;

    /**
     * @var bool
     */
    private $createSchema = true;

    /**
     * @var EventSubscriber[]
     */
    private $eventSubscribers;

    /**
     * Creates an infrastructure for the given entity or entities, including all
     * referenced entities.
     *
     * @param string[]|string $entityClassOrClasses
     * @param ConnectionConfiguration|null $connectionConfiguration Optional, specific database connection information.
     * @return ORMInfrastructure
     */
    public static function createWithDependenciesFor(
        $entityClassOrClasses,
        ConnectionConfiguration $connectionConfiguration = null
    ) {
        $entityClasses = static::normalizeEntityList($entityClassOrClasses);
        return new static(new EntityDependencyResolver($entityClasses), $connectionConfiguration);
    }

    /**
     * Creates an infrastructure for the given entity or entities.
     *
     * The infrastructure that is required for entities that are associated with the given
     * entities is *not* created automatically.
     *
     * @param string[]|string $entityClassOrClasses
     * @param ConnectionConfiguration|null $connectionConfiguration Optional, specific database connection information.
     * @return ORMInfrastructure
     */
    public static function createOnlyFor($entityClassOrClasses, ConnectionConfiguration $connectionConfiguration = null)
    {
        return new static(static::normalizeEntityList($entityClassOrClasses), $connectionConfiguration);
    }

    /**
     * Accepts a single entity class or a list of entity classes and always returns a
     * list of entity classes.
     *
     * @param string[]|string $entityClassOrClasses
     * @return string[]
     */
    protected static function normalizeEntityList($entityClassOrClasses)
    {
        $entityClasses = (is_string($entityClassOrClasses)) ? array($entityClassOrClasses) : $entityClassOrClasses;
        static::assertClassNames($entityClasses);
        return $entityClasses;
    }

    /**
     * Creates an entity helper that provides a database infrastructure
     * for the provided entities.
     *
     * Foreach entity the fully qualified class name must be provided.
     *
     * @param string[]|\Traversable $entityClasses
     * @param ConnectionConfiguration|null $connectionConfiguration Optional, specific database connection information.
     * @deprecated Use one of the create*For() factory methods.
     */
    public function __construct($entityClasses, ConnectionConfiguration $connectionConfiguration = null)
    {
        // Register the annotation loader before the dependency discovery process starts (if required).
        // This ensures that the annotation loader is available for the entity resolver that reads the annotations.
        $this->annotationLoader = $this->createAnnotationLoader();
        $this->addAnnotationLoaderToRegistry($this->annotationLoader);
        if ($entityClasses instanceof \Traversable) {
            $entityClasses = iterator_to_array($entityClasses);
        }
        if ($connectionConfiguration === null) {
            $connectionConfiguration = new ConnectionConfiguration($this->defaultConnectionParams);
        }
        $this->entityClasses           = $entityClasses;
        $this->connectionConfiguration = $connectionConfiguration;
        $this->queryLogger             = new DebugStack();
        $this->namingStrategy          = new DefaultNamingStrategy();
        $this->configFactory           = new ConfigurationFactory();
        $this->resolveTargetListener   = new ResolveTargetEntityListener();

        $this->eventSubscribers = [$this->resolveTargetListener];
    }

    public function addEventSubscriber(EventSubscriber $subscriber): void
    {
        $this->eventSubscribers[] = $subscriber;
    }

    public function disableSchemaCreation()
    {
        $this->createSchema = false;
    }

    /**
     * @param NamingStrategy $namingStrategy
     */
    public function setNamingStrategy(NamingStrategy $namingStrategy): void
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * Returns the repository for the provided entity.
     *
     * @param string|object $classNameOrEntity Class name of an entity or entity instance.
     * @return ObjectRepository
     */
    public function getRepository($classNameOrEntity)
    {
        $className = is_object($classNameOrEntity) ? get_class($classNameOrEntity) : $classNameOrEntity;
        return $this->getEntityManager()->getRepository($className);
    }

    /**
     * Returns the queries that have been executed so far.
     *
     * @return Query[]
     */
    public function getQueries()
    {
        return array_map(function (array $queryData) {
            return new Query(
                $queryData['sql'],
                (isset($queryData['params']) ? $queryData['params'] : array()),
                $queryData['executionMS']
            );
        }, $this->queryLogger->queries);
    }

    /**
     * Imports entities from the provided data source.
     *
     * The supported data sources are documented at \Webfactory\Doctrine\ORMTestInfrastructure\Importer::import().
     *
     * @param mixed $dataSource Callback, single entity, array of entities or file path.
     * @see \Webfactory\Doctrine\ORMTestInfrastructure\Importer::import()
     */
    public function import($dataSource)
    {
        $loggerWasEnabled = $this->queryLogger->enabled;
        $this->queryLogger->enabled = false;
        $importer = new Importer($this->copyEntityManager());
        $importer->import($dataSource);
        $this->queryLogger->enabled = $loggerWasEnabled;
    }

    /**
     * Returns the entity manager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if ($this->entityManager === null) {
            $loggerWasEnabled = $this->queryLogger->enabled;
            $this->queryLogger->enabled = false;
            $this->entityManager = $this->createEntityManager();
            $this->setupEventSubscribers();
            if ($this->createSchema) {
                $this->createSchemaForSupportedEntities();
            }
            $this->queryLogger->enabled = $loggerWasEnabled;
        }
        return $this->entityManager;
    }

    /**
     * Returns the event manager that will be used by the entity manager.
     *
     * Can be used to register type mappings for interfaces.
     *
     * @return EventManager
     * @internal Do not rely on this method if you don't have to. Might be removed in future versions.
     */
    public function getEventManager()
    {
        return $this->getEntityManager()->getEventManager();
    }

    /**
     * Registers a type mapping.
     *
     * Might be required if you define an association mapping against an interface.
     *
     * @param string $originalEntity
     * @param string $targetEntity
     * @throws \LogicException If you call this method after using the infrastructure.
     * @internal Might be replaced in the future by a more advanced config system.
     *           Do not rely on this feature if you don't have to.
     * @see http://symfony.com/doc/current/doctrine/resolve_target_entity.html#set-up
     */
    public function registerEntityMapping($originalEntity, $targetEntity)
    {
        if ($this->entityManager !== null) {
            $message = 'Call %s() before using the entity manager or importing data. '
                . 'Otherwise your entity mapping might not take effect.';
            throw new \LogicException(sprintf($message, __FUNCTION__));
        }
        $this->resolveTargetListener->addResolveTargetEntity($originalEntity, $targetEntity, array());
    }

    /**
     * Creates a new entity manager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function createEntityManager()
    {
        $config = $this->configFactory->createFor($this->entityClasses);
        $config->setSQLLogger($this->queryLogger);
        $config->setNamingStrategy($this->namingStrategy);

        return EntityManager::create(
            $this->connectionConfiguration->getConnectionParameters(),
            $config
        );
    }

    /**
     * Creates the schema for the managed entities.
     */
    protected function createSchemaForSupportedEntities()
    {
        $metadata   = $this->getMetadataForSupportedEntities();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema($metadata);
    }

    /**
     * Returns the metadata for each managed entity.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata[]
     */
    public function getMetadataForSupportedEntities()
    {
        $metadataFactory = $this->getEntityManager()->getMetadataFactory();
        $metadata = array();
        foreach ($this->entityClasses as $class) {
            $metadata[] = $metadataFactory->getMetadataFor($class);
        }
        return $metadata;
    }

    /**
     * Restores the state of the annotation registry.
     */
    public function __destruct()
    {
        $this->removeAnnotationLoaderFromRegistry($this->annotationLoader);
    }

    /**
     * Creates an annotation loader.
     *
     * The loader uses class_exists() to trigger the configured class loader.
     * This ensures that all loadable annotation classes can be used and avoid
     * dealing with annotation class white lists.
     *
     * @return \Closure
     */
    protected function createAnnotationLoader()
    {
        $loader = function ($annotationClass) {
            return class_exists($annotationClass, true);
        };
        // Starting with PHP 5.4, the object context is bound to created closures. The context is not needed
        // in the function above and as we will store the function in an attribute, this would create a
        // circular reference between object and function. That would delay the garbage collection and
        // the cleanup that happens in __destruct.
        // To avoid these issues, we simply remove the context from the lambda function.
        return $loader->bindTo(null);
    }

    /**
     * Adds a custom annotation loader to Doctrine's AnnotationRegistry.
     *
     * @param \Closure $loader
     */
    protected function addAnnotationLoaderToRegistry(\Closure $loader)
    {
        AnnotationRegistry::registerLoader($loader);
    }

    /**
     * Removes the loader that has been added to Doctrine's AnnotationRegistry.
     *
     * This requires some ugly reflection as the registry data is static and the loaders
     * are not publicly accessible.
     * Loaders are compared by identity, therefore, this will only work correctly with
     * \Closure instances.
     *
     * @param \Closure $loader The loader that will be removed.
     */
    protected function removeAnnotationLoaderFromRegistry(\Closure $loader)
    {
        $reflection = new \ReflectionClass(AnnotationRegistry::class);
        $annotationLoaderProperty = $reflection->getProperty('loaders');
        $annotationLoaderProperty->setAccessible(true);
        $activeLoaders = $annotationLoaderProperty->getValue();
        foreach ($activeLoaders as $index => $activeLoader) {
            /* @var $loader callable */
            if ($activeLoader === $loader) {
                unset($activeLoaders[$index]);
            }
        }
        $annotationLoaderProperty->setValue(array_values($activeLoaders));
    }

    /**
     * Creates a copy of the current entity manager.
     *
     * @return EntityManager
     */
    private function copyEntityManager()
    {
        $entityManager = $this->getEntityManager();

        return EntityManager::create(
            $entityManager->getConnection(),
            $entityManager->getConfiguration(),
            $this->getEventManager()
        );
    }

    private function setupEventSubscribers()
    {
        $eventManager = $this->getEventManager();

        foreach ($this->eventSubscribers as $subscriber) {
            $eventManager->addEventSubscriber($subscriber);
        }
    }

    /**
     * Checks if all entries in the given list are names of existing classes.
     *
     * @param string[] $classes
     * @throws \InvalidArgumentException If an entry is not a valid class name.
     */
    private static function assertClassNames(array $classes)
    {
        foreach ($classes as $class) {
            if (class_exists($class, true)) {
                continue;
            }
            $message = sprintf('"%s" is no existing class. Did you configure your autoloader correctly?', $class);
            throw new \InvalidArgumentException($message);
        }
    }
}
