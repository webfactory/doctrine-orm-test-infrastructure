<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

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
        $this->innerDriver->expects($this->any())
            ->method('getAllClassNames')
            ->will($this->returnValue(array(
                'My\Namespace\Person',
                'My\Namespace\Address',
                'My\Namespace\PhoneNumber'
            )));

        $classes = $this->driver->getAllClassNames();

        $this->assertInternalType('array', $classes);
        $this->assertContains('My\Namespace\Person', $classes);
        $this->assertContains('My\Namespace\Address', $classes);
        $this->assertNotContains('My\Namespace\PhoneNumber', $classes);
    }

    /**
     * Ensures that the driver decorator does not expose entity classes, which are listed, but
     * not supported by the iner driver.
     */
    public function testDriverDoesNotExposeEntitiesThatAreInListButNotSupportedByInnerDriver()
    {
        $this->innerDriver->expects($this->any())
            ->method('getAllClassNames')
            ->will($this->returnValue(array(
                // The inner driver supports Person, but not Address.
                'My\Namespace\Person'
            )));

        $classes = $this->driver->getAllClassNames();

        $this->assertInternalType('array', $classes);
        $this->assertContains('My\Namespace\Person', $classes);
        $this->assertNotContains('My\Namespace\Address', $classes);
    }

    public function testGetAllClassNamesWorksIfEntityClassWasPassedWithLeadingBackslash()
    {
        $this->driver = new EntityListDriverDecorator($this->innerDriver, array(
            // The entity class is passed with a leading slash.
            '\My\Namespace\Person'
        ));
        $this->innerDriver->expects($this->any())
            ->method('getAllClassNames')
            ->will($this->returnValue(array(
                'My\Namespace\Person'
            )));

        $classes = $this->driver->getAllClassNames();

        $this->assertInternalType('array', $classes);
        $this->assertContains('My\Namespace\Person', $classes);
    }

    public function testDriverDelegatesMetadataCalls()
    {
        $this->innerDriver->expects($this->once())
            ->method('loadMetadataForClass');

        $this->driver->loadMetadataForClass('My\Namespace\Person', new ClassMetadataInfo('My\Namespace\Person'));
    }

    public function testDriverDelegatesIsTransientCall()
    {
        $this->innerDriver->expects($this->once())
            ->method('isTransient');

        $this->driver->isTransient('My\Namespace\Person');
    }
}
