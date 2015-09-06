<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;

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
        $config = Setup::createConfiguration(
            // Activate development mode.
            true,
            // Store proxies in the default temp directory.
            null,
            // Avoid Doctrine auto-detection of cache and use an isolated cache.
            new ArrayCache()
        );
        $driver = new AnnotationDriver(
            $this->getAnnotationReader(),
            $this->getFilePathsForClassNames($entityClasses)
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
     * Returns the default annotation reader and creates it if necessary.
     *
     * @return Reader|AnnotationReader
     */
    protected function getAnnotationReader()
    {
        if (static::$defaultAnnotationReader === null) {
            $factory = new Configuration();
            // Use the configuration to create an annotation driver as the configuration
            // handles loading of default annotation automatically.
            $driver = $factory->newDefaultAnnotationDriver(array(), false);
            // Use just the reader as the driver depends on the configured
            // paths and, therefore, should not be shared.
            static::$defaultAnnotationReader = $driver->getReader();
        }
        return static::$defaultAnnotationReader;
    }
}
