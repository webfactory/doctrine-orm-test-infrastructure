<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\Inheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * Child class that uses class table inheritance with a parent that references another entity.
 *
 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#class-table-inheritance
 */
#[ORM\Table(name: 'class_inheritance_with_reference_child')]
#[ORM\Entity]
class ClassTableChildWithParentReferenceEntity extends ClassTableParentWithReferenceEntity
{
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'child_name', nullable: false)]
    public $childName = 'child-name';
}
