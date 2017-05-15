<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Tests the object manager decorator that detaches entities after persisting.
 *
 * @deprecated Will be removed in 2.0.
 */
class DetachingObjectManagerDecoratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * System under test.
     *
     * @var \Webfactory\Doctrine\ORMTestInfrastructure\DetachingObjectManagerDecorator
     */
    protected $decorator = null;

    /**
     * The (mocked) inner object manager.
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ObjectManagerDecorator
     */
    protected $objectManager = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = $this->getMock('\Doctrine\Common\Persistence\ObjectManagerDecorator');
        $this->decorator     = new DetachingObjectManagerDecorator($this->objectManager);
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->objectManager = null;
        $this->decorator     = null;
        parent::tearDown();
    }

    /**
     * Ensures that an entity is not detached directly when it is passed to persist()
     * as it must be flushed first.
     */
    public function testDecoratorDoesNotDetachDirectlyAfterPersistCall()
    {
        $this->objectManager->expects($this->never())
                            ->method('detach');

        $this->decorator->persist(new \stdClass());
    }

    /**
     * Checks if entities that have been passed to persist() are detached when
     * flush is called().
     */
    public function testDecoratorDetachesAllPersistedEntitiesOnFlush()
    {
        $this->objectManager->expects($this->exactly(2))
                            ->method('detach')
                            ->with($this->isInstanceOf('\stdClass'));

        $this->decorator->persist(new \stdClass());
        $this->decorator->persist(new \stdClass());
        $this->decorator->flush();
    }

    /**
     * Ensures that entities are detached only once, which means that the same entity
     * is not detached again on the next flush() call.
     */
    public function testOnceDetachedEntitiesAreNotDetachedAgainOnNextFlush()
    {
        $this->objectManager->expects($this->exactly(2))
                            ->method('detach')
                            ->with($this->isInstanceOf('\stdClass'));

        $this->decorator->persist(new \stdClass());
        $this->decorator->persist(new \stdClass());
        $this->decorator->flush();
        $this->decorator->flush();
    }
}
