<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\EntityListDriverDecorator;

class EntityListDriverDecoratorTest extends TestCase
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
     * @var MappingDriver|MockObject
     */
    protected $innerDriver = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->innerDriver = $this->createMock(MappingDriver::class);
        $this->driver      = new EntityListDriverDecorator($this->innerDriver, array(
            'My\Namespace\Person',
            'My\Namespace\Address'
        ));
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown(): void
    {
        $this->driver      = null;
        $this->innerDriver = null;
        parent::tearDown();
    }

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(MappingDriver::class, $this->driver);
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

        $this->assertIsArray($classes);
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

        $this->assertIsArray($classes);
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

        $this->assertIsArray($classes);
        $this->assertContains('My\Namespace\Person', $classes);
    }

    public function testDriverDelegatesMetadataCalls()
    {
        $this->innerDriver->expects($this->once())
            ->method('loadMetadataForClass');

        $this->driver->loadMetadataForClass('My\Namespace\Person', new ClassMetadata('My\Namespace\Person'));
    }

    public function testDriverDelegatesIsTransientCall()
    {
        $this->innerDriver->expects($this->once())
            ->method('isTransient')
            ->willReturn(false);

        $this->driver->isTransient('My\Namespace\Person');
    }
}
