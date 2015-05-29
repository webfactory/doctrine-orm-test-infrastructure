<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\Setup;

/**
 * Takes a set of entity classes and resolves to a set that contains all entities
 * that are referenced by the provided entity classes (via associations).
 *
 * The resolved set also includes the original entity classes.
 */
class EntityDependencyResolver implements \IteratorAggregate
{
    /**
     * Contains the names of the entity classes that were initially provided.
     *
     * @var string[]
     */
    protected $initialEntitySet = null;

    /**
     * Creates a resolver for the given entity classes.
     *
     * @param string[] $entityClasses
     */
    public function __construct(array $entityClasses)
    {
        $this->initialEntitySet = $this->normalizeClassNames($entityClasses);
    }

    /**
     * Allows iterating over the set of resolved entities.
     *
     * @return \Traversable
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->resolve($this->initialEntitySet));
    }

    /**
     * Resolves the dependencies for the given entities.
     *
     * @param string[] $entityClasses
     * @return string[]
     */
    protected function resolve(array $entityClasses)
    {
        $entitiesToCheck = $entityClasses;
        $config = $this->createConfigFor($entitiesToCheck);
        while (count($associatedEntities = $this->getDirectlyAssociatedEntities($config, $entitiesToCheck)) > 0) {
            $newAssociations = array_diff($associatedEntities, $entityClasses);
            $entityClasses = array_merge($entityClasses, $newAssociations);
            $config = $this->createConfigFor($entityClasses);
            $entitiesToCheck = $newAssociations;
        }
        return $entityClasses;
    }

    /**
     * Returns the class names of additional entities that are directly associated with
     * one of the entities that is explicitly mentioned in the given configuration.
     *
     * @param Configuration $config
     * @param string[] $entityClasses Classes whose associations are checked.
     * @return string[] Associated entity classes.
     */
    protected function getDirectlyAssociatedEntities(Configuration $config, $entityClasses)
    {
        if (count($entityClasses) === 0) {
            return array();
        }
        $associatedEntities = array();
        $reflection = new RuntimeReflectionService();
        foreach ($entityClasses as $entityClass) {
            /* @var $entityClass string */
            $metadata = new ClassMetadata($entityClass);
            $metadata->initializeReflection($reflection);
            $config->getMetadataDriverImpl()->loadMetadataForClass($entityClass, $metadata);
            foreach ($metadata->getAssociationNames() as $name) {
                /* @var $name string */
                $associatedEntity = $metadata->getAssociationTargetClass($name);
                $associatedEntities[] = $metadata->fullyQualifiedClassName($associatedEntity);
            }
        }
        return array_unique($associatedEntities);
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
            $this->getFilePathsForClassNames($entityClasses),
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
     * Returns a list of file paths for the provided class names.
     *
     * @param array(string) $classNames
     * @return array(string)
     */
    protected function getFilePathsForClassNames(array $classNames)
    {
        $paths = array();
        foreach ($classNames as $className) {
            $paths[] = $this->getFilePathForClassName($className);
        }
        return array_unique($paths);
    }

    /**
     * Returns the path to the directory that contains the given class.
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
     * Removes leading slashes from the given class names.
     *
     * @param string[] $entityClasses
     * @return string[]
     */
    protected function normalizeClassNames(array $entityClasses)
    {
        return array_map(function ($class) {
            return ltrim($class, '\\');
        }, $entityClasses);
    }
}
