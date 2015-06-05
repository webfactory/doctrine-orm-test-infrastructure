<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Tools\Setup;

/**
 * Creates ORM configurations for a set of entities.
 *
 * These configurations are meant for testing only.
 */
class ConfigurationFactory
{
    /**
     * Creates the ORM configuration for the given set of entities.
     *
     * @param string[] $entityClasses
     * @return \Doctrine\ORM\Configuration
     */
    public function createFor(array $entityClasses)
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
}
