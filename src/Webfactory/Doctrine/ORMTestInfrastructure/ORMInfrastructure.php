<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

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
 *     $infrastructure = new ORMInfrastructure(array(
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
     */
    protected $defaultConnectionParams = array(
        'driver'   => 'pdo_sqlite',
        'user'     => 'root',
        'password' => '',
        'memory'   => true
    );

    /**
     * List of entity classes that are manager by this helper.
     *
     * @var array(string)
     */
    protected $entityClasses;

    /**
     * Determines if entities, that are referenced by the explicitly mentioned
     * test entities, are set up automatically.
     *
     * @var boolean
     */
    protected $automaticallySetupDependencies;

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
     * Callback that is used to load non-Doctrine annotations.
     *
     * @var \Closure
     */
    protected $annotationLoader = null;

    /**
     * Creates an entity helper that provides a database infrastructure
     * for the provided entities.
     *
     * Foreach entity the fully qualified class name must be provided.
     *
     * @param array(string) $entityClasses
     * @param boolean $automaticallySetupDependencies Determines if associated entities are handled automatically.
     */
    public function __construct(array $entityClasses, $automaticallySetupDependencies = false)
    {
        $this->entityClasses    = $entityClasses;
        $this->automaticallySetupDependencies = $automaticallySetupDependencies;
        $this->annotationLoader = $this->createAnnotationLoader();
        $this->queryLogger      = new DebugStack();
        $this->addAnnotationLoaderToRegistry($this->annotationLoader);
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
        $importer = new Importer($this->getEntityManager());
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
            $this->createSchemaForSupportedEntities($this->entityManager);
            $this->queryLogger->enabled = $loggerWasEnabled;
        }
        return $this->entityManager;
    }

    /**
     * Returns a list of file paths for the provided class names.
     *
     * @param array(string) $classNames
     * @return array(string)
     */
    protected function getFilePathsForArrayOfClassNames(array $classNames)
    {
        $paths = array();
        foreach ($classNames as $className) {
            $paths[] = $this->getFilePathForClassName($className);
        }
        return array_unique($paths);
    }

    /**
     * Returns the file path for the provided class name
     *
     * @param string $className
     * @return string
     */
    protected function getFilePathForClassName($className)
    {
        $info = new \ReflectionClass($className);
        return dirname($info->getFileName());
    }

    /**
     * Creates a new entity manager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function createEntityManager()
    {
        $config = $this->createConfigFor($this->entityClasses);
        if ($this->automaticallySetupDependencies) {
            $entityClasses = $this->entityClasses;
            while (count($associatedEntities = $this->getDirectlyAssociatedEntities($config)) > 0) {
                $entityClasses = array_merge($entityClasses, $associatedEntities);
                $config = $this->createConfigFor($entityClasses);
            }
        }
        $config->setSQLLogger($this->queryLogger);
        return EntityManager::create($this->defaultConnectionParams, $config);
    }

    /**
     * Creates the schema for the managed entities.
     *
     * @param EntityManager $entityManager
     */
    protected function createSchemaForSupportedEntities(EntityManager $entityManager)
    {
        $metadata   = $this->getMetadataForSupportedEntities($entityManager->getMetadataFactory());
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($metadata);
    }

    /**
     * Returns the metadata for each managed entity.
     *
     * @param ClassMetadataFactory $metadataFactory
     * @return array(\Doctrine\Common\Persistence\Mapping\ClassMetadata)
     */
    protected function getMetadataForSupportedEntities(ClassMetadataFactory $metadataFactory)
    {
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
        return function ($annotationClass) {
            return class_exists($annotationClass, true);
        };
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
        $reflection = new \ReflectionClass('\Doctrine\Common\Annotations\AnnotationRegistry');
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
     * Creates a Doctrine configuration for the given entity classes.
     *
     * @param string[] $entityClasses
     * @return \Doctrine\ORM\Configuration
     */
    protected function createConfigFor(array $entityClasses)
    {
        $config = Setup::createAnnotationMetadataConfiguration(
            $this->getFilePathsForArrayOfClassNames($entityClasses),
            // Activate development mode.
            true,
            // Store proxies in the default temp directory.
            null,
            // Avoid Doctrine auto-detection of cache and use an isolated cache.
            new ArrayCache(),
            false
        );
        return $config;
    }

    /**
     * Returns the class names of additional entities that are directly associated with
     * one of the entities that is explicitly mentioned in the given configuration.
     *
     * @param Configuration $config
     * @return string[] Associated entity classes.
     */
    protected function getDirectlyAssociatedEntities(Configuration $config)
    {
        $associatedEntities = array();
        // TODO: getAllClassNames returns *all* entities in the configured directories
        foreach ($config->getMetadataDriverImpl()->getAllClassNames() as $entityClass) {
            /* @var $entityClass string */
            $metadata = new ClassMetadata($entityClass);
            $config->getMetadataDriverImpl()->loadMetadataForClass($entityClass, $metadata);
            foreach ($metadata->getAssociationMappings() as $mapping) {
                /* @var $mapping array */
                $associatedEntities[] = $mapping['targetEntity'];
            }
        }
        return array();
    }
}
