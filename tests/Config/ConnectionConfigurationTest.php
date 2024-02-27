<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\Config;

use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\Config\ConnectionConfiguration;

class ConnectionConfigurationTest extends TestCase
{
    /**
     * System under test.
     *
     * @var ConnectionConfiguration
     */
    private $connectionConfiguration = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->connectionConfiguration = new ConnectionConfiguration(array(
            'driver'   => 'pdo_sqlite',
            'user'     => 'root',
            'password' => '',
            'memory'   => true
        ));
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown(): void
    {
        $this->connectionConfiguration = null;
        parent::tearDown();
    }

    public function testGetConnectionParametersReturnsProvidedValues()
    {
        $params = $this->connectionConfiguration->getConnectionParameters();

        $this->assertIsArray($params);
        $expectedParams = array(
            'driver'   => 'pdo_sqlite',
            'user'     => 'root',
            'password' => '',
            'memory'   => true
        );
        foreach ($expectedParams as $param => $value) {
            $this->assertArrayHasKey($param, $params, 'Connection parameter missing.');
            $this->assertEquals($value, $params[$param], 'Unexpected value for connection parameter "' . $param . '".');
        }
    }
}
