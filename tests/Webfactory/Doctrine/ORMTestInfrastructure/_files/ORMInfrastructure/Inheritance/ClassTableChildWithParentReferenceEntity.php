<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance;

/**
 * Child class that uses class table inheritance with a parent that references another entity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="class_inheritance_with_reference_child")
 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#class-table-inheritance
 */
class ClassTableChildWithParentReferenceEntity
{
    /**
     * @var string
     * @ORM\Column(type="string", name="child_name", nullable=false)
     */
    public $childName = 'child-name';
}
