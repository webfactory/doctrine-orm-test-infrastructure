<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Tests the ORM configuration factory.
 */
class ConfigurationFactoryTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        parent::setUp();
        $this->factory = new ConfigurationFactory();
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->factory = null;
        parent::tearDown();
    }
    /**
     * Ensures that createFor() returns an ORM configuration object.
     */
    public function testCreateForReturnsConfiguration()
    {
        $configuration = $this->factory->createFor(array(
            '\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity'
        ));

        $this->assertInstanceOf('\Doctrine\ORM\Configuration', $configuration);
    }
}
