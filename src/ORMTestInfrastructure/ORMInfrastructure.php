<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware as LoggingMiddleware;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Webfactory\Doctrine\Config\ConnectionConfiguration;
use Webfactory\Doctrine\Config\ExistingConnectionConfiguration;
use Webfactory\Doctrine\Logging\Middleware;
use Webfactory\Doctrine\Logging\Query;

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
     * @var Middleware
     */
    protected $queryLogger = null;

    /**
     * The naming strategy that is used.
     *
     * @var NamingStrategy
     */
    protected $namingStrategy = null;

    private readonly ?MappingDriver $mappingDriver;

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
    public static function createWithDependenciesFor($entityClassOrClasses, ?ConnectionConfiguration $connectionConfiguration = null, ?MappingDriver $mappingDriver = null) {
        $entityClasses = static::normalizeEntityList($entityClassOrClasses);
        return new static(new EntityDependencyResolver($entityClasses, $mappingDriver), $connectionConfiguration, $mappingDriver);
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
    public static function createOnlyFor($entityClassOrClasses, ?ConnectionConfiguration $connectionConfiguration = null, ?MappingDriver $mappingDriver = null)
    {
        return new static(static::normalizeEntityList($entityClassOrClasses), $connectionConfiguration, $mappingDriver);
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
     */
    private function __construct($entityClasses, ?ConnectionConfiguration $connectionConfiguration = null, ?MappingDriver $mappingDriver = null)
    {
        if ($entityClasses instanceof \Traversable) {
            $entityClasses = iterator_to_array($entityClasses);
        }
        if ($connectionConfiguration === null) {
            $connectionConfiguration = new ConnectionConfiguration([
                'driver' => 'pdo_sqlite',
                'user' => 'root',
                'password' => '',
                'memory' => true,
            ]);
        }
        $this->entityClasses           = $entityClasses;
        $this->connectionConfiguration = $connectionConfiguration;
        $this->queryLogger             = new Middleware();
        $this->namingStrategy          = new DefaultNamingStrategy();
        $this->mappingDriver           = $mappingDriver;
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
        return $this->queryLogger->getQueries();
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
        $loggerWasEnabled = $this->queryLogger->isEnabled();
        $this->queryLogger->setEnabled(false);
        $importer = new Importer($this->copyEntityManager());
        $importer->import($dataSource);
        $this->queryLogger->setEnabled($loggerWasEnabled);
    }

    /**
     * Returns the entity manager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if ($this->entityManager === null) {
            $loggerWasEnabled = $this->queryLogger->isEnabled();
            $this->queryLogger->setEnabled(false);
            $this->entityManager = $this->createEntityManager();
            $this->setupEventSubscribers();
            if ($this->createSchema) {
                $this->createSchemaForSupportedEntities();
            }
            $this->queryLogger->setEnabled($loggerWasEnabled);
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
        $configFactory = new ConfigurationFactory($this->mappingDriver);
        $config = $configFactory->createFor($this->entityClasses);
        $middlewares = $config->getMiddlewares();
        $middlewares[] = new LoggingMiddleware($this->queryLogger);
        $config->setMiddlewares($middlewares);
        $config->setNamingStrategy($this->namingStrategy);

        if ($this->connectionConfiguration instanceof ExistingConnectionConfiguration) {
            $connection = $this->connectionConfiguration->getConnection();
        } else {
            $connection = DriverManager::getConnection($this->connectionConfiguration->getConnectionParameters(), $config);
        }

        return new EntityManager($connection, $config);
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
     * Creates a copy of the current entity manager.
     *
     * @return EntityManager
     */
    private function copyEntityManager()
    {
        $entityManager = $this->getEntityManager();

        return new EntityManager(
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
