<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

class EntityListDriverDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System under test.
     *
     * @var EntityListDriverDecorator
     */
    protected $driver = null;

    /**
     * The mocked, decorated driver.
     *
     * @var MappingDriver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $innerDriver = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->innerDriver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver');
        $this->driver      = new EntityListDriverDecorator($this->innerDriver, array(
            'My\Namespace\Person',
            'My\Namespace\Address'
        ));
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->driver      = null;
        $this->innerDriver = null;
        parent::tearDown();
    }

    public function testImplementsInterface()
    {
        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver', $this->driver);
    }

    public function testGetAllClassNamesReturnsOnlyExposedEntityClasses()
    {

    }

    public function testDriverDelegatesMetadataCalls()
    {

    }

    public function testDriverDelegatesIsTransientCall()
    {

    }
}
