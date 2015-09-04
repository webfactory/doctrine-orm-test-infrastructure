<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class MappedSuperClassChild extends MappedSuperClassParentWithReference
{
    /**
     * @var string
     * @ORM\Column(type="string", name="child_name", nullable=false)
     */
    public $childName = 'child-name';
}
