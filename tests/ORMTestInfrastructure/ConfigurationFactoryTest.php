<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure;

use Doctrine\ORM\Configuration;
use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ConfigurationFactory;
use Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\TestEntity;

/**
 * Tests the ORM configuration factory.
 */
class ConfigurationFactoryTest extends TestCase
{
    /**
     * System under test.
     *
     * @var ConfigurationFactory
     */
    protected $factory = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ConfigurationFactory();
    }

    /**
     * Ensures that createFor() returns an ORM configuration object.
     */
    public function testCreateForReturnsConfiguration()
    {
        $configuration = $this->factory->createFor(array(
            TestEntity::class,
        ));

        $this->assertInstanceOf(Configuration::class, $configuration);
    }
}
