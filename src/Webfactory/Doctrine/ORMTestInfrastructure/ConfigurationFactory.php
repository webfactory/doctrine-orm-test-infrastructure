<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\ORMSetup;

/**
 * Creates ORM configurations for a set of entities.
 *
 * These configurations are meant for testing only.
 */
class ConfigurationFactory
{
    /**
     * Shared annotation reader or null, if not created yet.
     *
     * A shared reader is used for performance reasons. As annotations cannot
     * change during a test run, it is save to use a shared reader.
     *
     * @var Reader|null
     */
    protected static $defaultAnnotationReader = null;

    /**
     * Creates the ORM configuration for the given set of entities.
     *
     * @param string[] $entityClasses
     * @return \Doctrine\ORM\Configuration
     */
    public function createFor(array $entityClasses)
    {
        $config = ORMSetup::createConfiguration(true, null, new ArrayCachePool());

        $driver = new AnnotationDriver(
            $this->getAnnotationReader(),
            $this->getDirectoryPathsForClassNames($entityClasses)
        );
        $driver = new EntityListDriverDecorator($driver, $entityClasses);
        $config->setMetadataDriverImpl($driver);

        return $config;
    }

    /**
     * Returns a list of file paths for the provided class names.
     *
     * @param string[] $classNames
     * @return string[]
     */
    protected function getDirectoryPathsForClassNames(array $classNames)
    {
        $paths = array();
        foreach ($classNames as $className) {
            $paths[] = $this->getDirectoryPathForClassName($className);
        }
        return array_unique($paths);
    }

    /**
     * Returns the path to the directory that contains the given class.
     *
     * @param string $className
     * @return string
     */
    protected function getDirectoryPathForClassName($className)
    {
        $info = new \ReflectionClass($className);
        return dirname($info->getFileName());
    }

    /**
     * Returns the default annotation reader and creates it if necessary.
     *
     * @return Reader|AnnotationReader
     */
    protected function getAnnotationReader()
    {
        if (static::$defaultAnnotationReader === null) {
            // Use just the reader as the driver depends on the configured
            // paths and, therefore, should not be shared.
            static::$defaultAnnotationReader = ORMSetup::createDefaultAnnotationDriver()->getReader();
        }

        return static::$defaultAnnotationReader;
    }
}
