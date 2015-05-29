<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

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

    }
}
