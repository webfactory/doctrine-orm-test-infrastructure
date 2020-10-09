<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Config;

use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;

class InMemoryDatabaseConnectionConfigurationTest extends TestCase
{
    /**
     * Checks if the connection configuration *really* works with the infrastructure.
     */
    public function testWorksWithInfrastructure()
    {
        $configuration = new InMemoryDatabaseConnectionConfiguration();

        $infrastructure = ORMInfrastructure::createOnlyFor(TestEntity::class, $configuration);
        $this->assertNull(
            $infrastructure->import(new TestEntity())
        );
    }
}
