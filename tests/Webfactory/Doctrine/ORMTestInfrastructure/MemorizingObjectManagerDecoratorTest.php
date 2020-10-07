<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

use Doctrine\Persistence\ObjectManagerDecorator;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Will be removed in 2.0.
 * @see \Webfactory\Doctrine\ORMTestInfrastructure\MemorizingObjectManagerDecorator
 */
class MemorizingObjectManagerDecoratorTest extends TestCase
{
    /**
     * System under test.
     *
     * @var MemorizingObjectManagerDecorator
     */
    private $decorator = null;

    /**
     * The (mocked) inner object manager.
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Persistence\ObjectManagerDecorator
     */
    private $objectManager = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = $this->createMock(ObjectManagerDecorator::class);
        $this->decorator     = new MemorizingObjectManagerDecorator($this->objectManager);
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown(): void
    {
        $this->objectManager = null;
        $this->decorator     = null;
        parent::tearDown();
    }

    public function testGetSeenEntitiesReturnsPersistedEntities()
    {
        $first = new \stdClass();
        $second = new \stdClass();
        $this->decorator->persist($first);
        $this->decorator->persist($second);

        $seen = $this->decorator->getSeenEntities();

        $this->assertIsArray($seen);
        $this->assertContains($first, $seen);
        $this->assertContains($second, $seen);
        $this->assertCount(2, $seen);
    }
}
