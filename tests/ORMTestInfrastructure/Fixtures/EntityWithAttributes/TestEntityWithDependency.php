<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAttributes;

use Doctrine\ORM\Mapping as ORM;
use Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\ReferencedEntity;

#[ORM\Entity]
class TestEntityWithDependency
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\OneToOne(targetEntity: ReferencedEntity::class, cascade: ["all"])]
    #[ORM\JoinColumn(nullable: false)]
    public ?ReferencedEntity $dependency = null;

    public function __construct()
    {
        $this->dependency = new ReferencedEntity();
    }
}
