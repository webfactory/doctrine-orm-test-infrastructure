<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

/**
 * Driver decorator that restricts metadata access to a defined list of entities.
 *
 * @see https://github.com/webfactory/doctrine-orm-test-infrastructure/issues/11
 */
class EntityListDriverDecorator implements MappingDriver
{
    /**
     * The decorated driver.
     *
     * @var MappingDriver
     */
    protected $innerDriver = null;

    /**
     * Class names of all entities that are exposed.
     *
     * @var string[]
     */
    protected $exposedEntityClasses = null;

    /**
     * @param MappingDriver $innerDriver
     * @param string[] $exposedEntityClasses
     */
    public function __construct(MappingDriver $innerDriver, array $exposedEntityClasses)
    {
        $this->innerDriver = $innerDriver;
        $this->exposedEntityClasses = $exposedEntityClasses;
    }

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames()
    {
        return array_intersect(
            $this->exposedEntityClasses,
            $this->innerDriver->getAllClassNames()
        );
    }

    /**
     * Loads the metadata for the specified class into the provided container.
     *
     * @param string $className
     * @param ClassMetadata $metadata
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $this->innerDriver->loadMetadataForClass($className, $metadata);
    }

    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped as an Entity or a MappedSuperclass.
     *
     * @param string $className
     * @return boolean
     */
    public function isTransient($className)
    {
        return $this->innerDriver->isTransient($className);
    }
}
