<?php

namespace Webfactory\Doctrine\Tools;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

/**
 * Helper class that creates the database infrastructure for a defined
 * set of entity classes.
 *
 * Per default the required database is created in memory (via SQLite).
 * This provides full isolation and allows testing repositories and
 * entities against a real database.
 */
class EntityHelper
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
     * Creates an entity helper that provides a database infrastructure
     * for the provided entities.
     *
     * Foreach entity the fully qualified class name must be provided.
     *
     * @param array(string) $entityClasses
     * @param array $nonDoctrineAnnotationClasses If you use non-Doctrine annotations, provide an array with their fully
     * qualified class names.
     */
    public function __construct(array $entityClasses, array $nonDoctrineAnnotationClasses = array())
    {
        $this->entityClasses = $entityClasses;

        foreach ($nonDoctrineAnnotationClasses as $className) {
            AnnotationRegistry::registerAutoloadNamespace($className, $this->getFilePathForClassName($className));
        }
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
     * Returns the repository for the provided entity class.
     *
     * @param string $forClassName Class name of the entity.
     * @return ObjectRepository
     */
    public function getRepository($forClassName)
    {
        return $this->getEntityManager()->getRepository($forClassName);
    }

    /**
     * Uses the provided callback function to add entities to the database.
     *
     * The callback function retrieves the object manager as argument.
     * The object manager is used to persist new entities.
     *
     * Example:
     *
     *     $helper->load(function (\Doctrine\Common\Persistence\ObjectManager $manager) {
     *         $entity = new \My\Managed\Entity();
     *         $entity->setName('Max Power');
     *         $manager->persist($entity);
     *     });
     *
     * @param callable $callback
     * @deprecated Use import() to add entities.
     */
    public function load($callback)
    {
        $this->import($callback);
    }

    /**
     * Imports entities from the provided data source.
     *
     * The supported data sources are documented at \Webfactory\Doctrine\Tools\Importer::import().
     *
     * @param mixed $dataSource
     * @see \Webfactory\Doctrine\Tools\Importer::import()
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
     * Creates a new entity manager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function createEntityManager()
    {
        $config = Setup::createAnnotationMetadataConfiguration(
            $this->getFilePathsForArrayOfClassNames($this->entityClasses),
            true,             // Activate development mode.
            null,             // Store proxies in the default temp directory.
            new ArrayCache(), // Avoid Doctrine auto-detection of cache and use an isolated cache.
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

}
