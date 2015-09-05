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
     * @param MappingDriver $innerDriver
     * @param string[] $exposedEntityClasses
     */
    public function __construct(MappingDriver $innerDriver, array $exposedEntityClasses)
    {

    }

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames()
    {
        // TODO: Implement getAllClassNames() method.
    }

    /**
     * Loads the metadata for the specified class into the provided container.
     *
     * @param string $className
     * @param ClassMetadata $metadata
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        // TODO: Implement loadMetadataForClass() method.
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
        // TODO: Implement isTransient() method.
    }
}
