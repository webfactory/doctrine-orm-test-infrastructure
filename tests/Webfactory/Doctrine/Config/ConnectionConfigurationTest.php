<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Config;

class ConnectionConfigurationTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
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
    protected function tearDown()
    {
        $this->connectionConfiguration = null;
        parent::tearDown();
    }

    public function testGetConnectionParametersReturnsProvidedValues()
    {
        $params = $this->connectionConfiguration->getConnectionParameters();

        $this->assertInternalType('array', $params);
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
