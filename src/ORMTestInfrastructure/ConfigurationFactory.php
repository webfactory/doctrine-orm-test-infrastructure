<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Deprecations\Deprecation;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Creates ORM configurations for a set of entities.
 *
 * These configurations are meant for testing only.
 */
class ConfigurationFactory
{
    /** @var ?CacheItemPoolInterface */
    private static $metadataCache = null;

    /** @var ?MappingDriver */
    private $mappingDriver;

    public function __construct(?MappingDriver $mappingDriver = null)
    {
        $this->mappingDriver = $mappingDriver;
    }

    /**
     * Creates the ORM configuration for the given set of entities.
     *
     * @param string[] $entityClasses
     * @return \Doctrine\ORM\Configuration
     */
    public function createFor(array $entityClasses)
    {
        if (self::$metadataCache === null) {
            self::$metadataCache = new ArrayAdapter();
        }

        $mappingDriver = $this->mappingDriver ?? $this->createDefaultMappingDriver($entityClasses);

        $config = ORMSetup::createConfiguration(true, null, new ArrayAdapter());
        $config->setMetadataCache(self::$metadataCache);
        $config->setMetadataDriverImpl(new EntityListDriverDecorator($mappingDriver, $entityClasses));

        return $config;
    }

    /**
     * @param list<class-string> $entityClasses
     *
     * @return MappingDriver
     */
    private function createDefaultMappingDriver(array $entityClasses)
    {
        $paths = $this->getDirectoryPathsForClassNames($entityClasses);

        return new AttributeDriver($paths);
    }

    /**
     * Returns a list of file paths for the provided class names.
     *
     * @param list<class-string> $classNames
     * @return list<string>
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
     * @param class-string $className
     * @return string
     */
    protected function getDirectoryPathForClassName($className)
    {
        $info = new \ReflectionClass($className);
        return dirname($info->getFileName());
    }
}
