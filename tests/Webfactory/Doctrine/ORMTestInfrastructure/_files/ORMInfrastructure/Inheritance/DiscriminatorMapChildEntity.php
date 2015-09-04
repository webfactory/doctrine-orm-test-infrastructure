<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class DiscriminatorMapChildEntity extends DiscriminatorMapEntity
{
    /**
     * @var string
     * @ORM\Column(type="string", name="child_name", nullable=false)
     */
    public $childName = 'child-name';
}
