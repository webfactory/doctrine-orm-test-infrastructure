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
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

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
     * @param 'annotation'|'attribute'|'xml'|'yaml' $driverType
     * @return \Doctrine\ORM\Configuration
     */
    public function createFor(array $entityClasses, string $driverType = 'annotation')
    {
        $config = Setup::createConfiguration(
            // Activate development mode.
            true,
            // Store proxies in the default temp directory.
            null,
            // Avoid Doctrine auto-detection of cache and use an isolated cache.
            DoctrineProvider::wrap(new ArrayAdapter())
        );
        $paths = $this->getDirectoryPathsForClassNames($entityClasses);
        switch ($driverType) {
            case 'annotation':
                $driver = new AnnotationDriver($this->getAnnotationReader(),$paths);
                break;
            case 'attribute':
                $driver = new AttributeDriver($paths, true);
                break;
            case 'xml':
                $driver = new XmlDriver($paths, XmlDriver::DEFAULT_FILE_EXTENSION, true);
                break;
            case 'yaml':
                $driver = new YamlDriver($paths);
                break;
            default:
                throw new \LogicException("Unsupported drive type $driverType");
        }

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
            $factory = new Configuration();
            // Use the configuration to create an annotation driver as the configuration
            // handles loading of default annotations automatically.
            $driver = $factory->newDefaultAnnotationDriver(array(), false);
            // Use just the reader as the driver depends on the configured
            // paths and, therefore, should not be shared.
            static::$defaultAnnotationReader = $driver->getReader();
        }
        return static::$defaultAnnotationReader;
    }
}
