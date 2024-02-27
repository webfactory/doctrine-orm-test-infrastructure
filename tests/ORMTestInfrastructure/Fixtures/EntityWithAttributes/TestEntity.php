<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAttributes;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TestEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column(nullable: true)]
    public ?string $name = null;
}
