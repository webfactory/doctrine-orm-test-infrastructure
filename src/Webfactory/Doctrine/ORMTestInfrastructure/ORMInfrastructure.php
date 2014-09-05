<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
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
     * The entity manager that is used to perform entity operations.
     *
     * Contains null if the entity manager has not been created yet.
     *
     * @var \Doctrine\ORM\EntityManager|null
     */
    protected $entityManager = null;

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
     */
    public function __construct(array $entityClasses)
    {
        $this->entityClasses    = $entityClasses;
        $this->annotationLoader = $this->createAnnotationLoader();
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
     * Imports entities from the provided data source.
     *
     * The supported data sources are documented at \Webfactory\Doctrine\ORMTestInfrastructure\Importer::import().
     *
     * @param mixed $dataSource Callback, single entity, array of entities or file path.
     * @see \Webfactory\Doctrine\ORMTestInfrastructure\Importer::import()
     */
    public function import($dataSource)
    {
        $importer = new Importer($this->getEntityManager());
        $importer->import($dataSource);
    }

    /**
     * Returns the entity manager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if ($this->entityManager === null) {
            $this->entityManager = $this->createEntityManager();
            $this->createSchemaForSupportedEntities($this->entityManager);
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
        $config = Setup::createAnnotationMetadataConfiguration(
            $this->getFilePathsForArrayOfClassNames($this->entityClasses),
            // Activate development mode.
            true,
            // Store proxies in the default temp directory.
            null,
            // Avoid Doctrine auto-detection of cache and use an isolated cache.
            new ArrayCache(),
            false
        );
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
}
