<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\ReflectionService;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;

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
     * Service that is used to inspect entity classes.
     *
     * @var ReflectionService
     */
    protected $reflectionService = null;

    /**
     * Factory that is used to create ORM configurations.
     *
     * @var ConfigurationFactory
     */
    protected $configFactory = null;

    /**
     * Creates a resolver for the given entity classes.
     *
     * @param string[] $entityClasses
     */
    public function __construct(array $entityClasses)
    {
        $this->initialEntitySet  = $this->normalizeClassNames($entityClasses);
        $this->reflectionService = new RuntimeReflectionService();
        $this->configFactory     = new ConfigurationFactory();
    }

    /**
     * Allows iterating over the set of resolved entities.
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     */
    public function getIterator(): \Traversable
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
        $config = $this->configFactory->createFor($entitiesToCheck);
        while (count($associatedEntities = $this->getDirectlyAssociatedEntities($config, $entitiesToCheck)) > 0) {
            $associatedEntities = $this->removeInterfaces($associatedEntities);
            $newAssociations    = array_diff($associatedEntities, $entityClasses);
            $entityClasses      = array_merge($entityClasses, $newAssociations);
            $config             = $this->configFactory->createFor($entityClasses);
            $entitiesToCheck    = $newAssociations;
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
        $associatedEntities = [];
        $mappingDriver = $config->getMetadataDriverImpl();

        foreach ($entityClasses as $entityClass) {
            /* @var $entityClass string */
            $metadata = new ClassMetadata($entityClass);
            $metadata->initializeReflection($this->reflectionService);
            $mappingDriver->loadMetadataForClass($entityClass, $metadata);

            foreach ($metadata->getAssociationNames() as $name) {
                /* @var $name string */
                $associatedEntity = $metadata->getAssociationTargetClass($name);
                $associatedEntities[$metadata->fullyQualifiedClassName($associatedEntity)] = true;
            }

            if ($metadata->isInheritanceTypeJoined()) {
                foreach ($metadata->discriminatorMap as $childClass) {
                    $associatedEntities[$childClass] = true;
                }
            }

            // Add parent classes that are involved in some kind of entity inheritance.
            $parentClassesTowardsInheritanceBaseTable = [];
            foreach ($this->reflectionService->getParentClasses($entityClass) as $parentClass) {
                if ($mappingDriver->isTransient($parentClass)) {
                    continue;
                }

                $parentClassesTowardsInheritanceBaseTable[] = $parentClass;
                $metadata = new ClassMetadata($parentClass);
                $metadata->initializeReflection($this->reflectionService);
                $mappingDriver->loadMetadataForClass($parentClass, $metadata);
                if ($metadata->isInheritanceTypeNone()) {
                    continue;
                }

                foreach ($parentClassesTowardsInheritanceBaseTable as $class) {
                    $associatedEntities[$class] = true;
                }
                $parentClassesTowardsInheritanceBaseTable = [];
            }
        }
        return array_keys($associatedEntities);
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

    /**
     * Returns all interfaces from the given list of entity types.
     *
     * Interfaces can be defined as association targets, but this simple resolver cannot handle them properly.
     * Interfaces need additional configuration to be resolved to real entity classes.
     *
     * @param string[] $entityTypes
     * @return string[]
     */
    private function removeInterfaces($entityTypes)
    {
        return array_filter(
            $entityTypes,
            function ($entity) {
                return !interface_exists($entity);
            }
        );
    }

    private function fetchMetadata(string $entityClass, MappingDriver $mappingDriver): ClassMetadata
    {
        $metadata = new ClassMetadata($entityClass);
        $metadata->initializeReflection($this->reflectionService);
        $mappingDriver->loadMetadataForClass($entityClass, $metadata);

        return $metadata;
    }
}
