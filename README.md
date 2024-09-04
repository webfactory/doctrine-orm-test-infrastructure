doctrine-orm-test-infrastructure
================================

![Tests](https://github.com/webfactory/doctrine-orm-test-infrastructure/workflows/Tests/badge.svg)

This library provides some infrastructure for tests of Doctrine ORM entities, featuring:

- configuration of a SQLite in memory database, compromising well between speed and a database environment being both
  realistic and isolated
- a mechanism for importing fixtures into your database that circumvents Doctrine's caching. This results in a more
  realistic test environment when loading entities from a repository.

[We](https://www.webfactory.de/) use it to test Doctrine repositories and entities in Symfony applications. It's a
lightweight alternative to the
heavyweight [functional tests suggested in the Symfony documentation](http://symfony.com/doc/current/cookbook/testing/doctrine.html)
(we don't suggest you should skip those - we just want to open another path).

In non-application bundles, where functional tests are not possible,
it is our only way to test repositories and entities.

Installation
------------

Install via composer (see http://getcomposer.org/):

    composer require --dev webfactory/doctrine-orm-test-infrastructure

Usage
-----

```php
<?php

use Doctrine\ORM\EntityManagerInterface;
use Entity\MyEntity;
use Entity\MyEntityRepository;
use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;

class MyEntityRepositoryTest extends TestCase
{
    private ORMInfrastructure $infrastructure;
    private MyEntityRepository $repository;

    protected function setUp(): void
    {
        /*
           This will create an in-memory SQLite database with the necessary schema
           for the MyEntity entity class and and everything reachable from it through
           associations.
        */
        $this->infrastructure = ORMInfrastructure::createWithDependenciesFor(MyEntity::class);
        $this->repository = $this->infrastructure->getRepository(MyEntity::class);
    }

    /**
     * Example test: Asserts imported fixtures are retrieved with findAll().
     */
    public function testFindAllRetrievesFixtures(): void
    {
        $myEntityFixture = new MyEntity();

        $this->infrastructure->import($myEntityFixture);
        $entitiesLoadedFromDatabase = $this->repository->findAll();

        /* 
            import() will use a dedicated entity manager, so imported entities do not
            end up in the identity map. But this also means loading entities from the
            database will create _different object instances_.

            So, this does not hold:
        */
        // self::assertContains($myEntityFixture, $entitiesLoadedFromDatabase);

        // But you can do things like this (you probably want to extract that in a convenient assertion method):
        self::assertCount(1, $entitiesLoadedFromDatabase);
        $entityLoadedFromDatabase = $entitiesLoadedFromDatabase[0];
        self::assertSame($myEntityFixture->getId(), $entityLoadedFromDatabase->getId());
    }

    /**
     * Example test for retrieving Doctrine's entity manager.
     */
    public function testSomeFancyThingWithEntityManager(): void
    {
        $entityManager = $this->infrastructure->getEntityManager();
        // ...
    }
}
```

Migrating to attribute-based mapping configuration
--------------------------------------------------

The `ORMInfrastructure::createWithDependenciesFor()` and ``ORMInfrastructure::createOnlyFor()` methods by default
assume that the Doctrine ORM mapping is provided through annotations. This has been deprecated in Doctrine ORM 2.x
and is no longer be supported in ORM 3.0.

To allow for a seamless transition towards attribute-based or other types of mapping, a mapping driver can be passed
when creating instances of the `ORMInfrastructure`.

If you wish to switch to attribute-based mappings, pass a `new \Doctrine\ORM\Mapping\Driver\AttributeDriver($paths)`,
where `$paths` is an array of directory paths where your entity classes are stored.

For hybrid (annotations and attributes) mapping configurations, you can use `\Doctrine\Persistence\Mapping\Driver\MappingDriverChain`.
Multiple mapping drivers can be registered on the driver chain by providing namespace prefixes. For every namespace prefix,
only one mapping driver can be used.

Testing the library itself
--------------------------

After installing the dependencies managed via composer, just run

    vendor/bin/phpunit

from the library's root folder. This uses the shipped phpunit.xml.dist - feel free to create your own phpunit.xml if you
need local changes.

Happy testing!

Known Issues
------------

Please note that apart from any [open issues in this library](https://github.com/webfactory/doctrine-orm-test-infrastructure/issues), you
may stumble upon any Doctrine issues. Especially take care of
it's [known sqlite issues](http://doctrine-dbal.readthedocs.org/en/latest/reference/known-vendor-issues.html#sqlite).

Credits, Copyright and License
------------------------------

This package was first written by webfactory GmbH (Bonn, Germany) and received [contributions
from other people](https://github.com/webfactory/doctrine-orm-test-infrastructure/graphs/contributors) since then.

webfactory is a software development agency with a focus on PHP (mostly [Symfony](http://github.com/symfony/symfony)).
If you're a developer looking for new challenges, we'd like to hear from you!

- <https://www.webfactory.de>

Copyright 2012 – 2024 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
