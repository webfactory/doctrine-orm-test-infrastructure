<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\Inheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * Child class that uses class table inheritance.
 *
 * @ORM\Entity()
 * @ORM\Table(name="class_inheritance_child")
 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#class-table-inheritance
 */
class ClassTableChildEntity extends ClassTableParentEntity
{
    /**
     * @var string
     * @ORM\Column(type="string", name="child_name", nullable=false)
     */
    public $childName = 'child-name';
}
