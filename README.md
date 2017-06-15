doctrine-orm-test-infrastructure
================================

[![Build Status](https://travis-ci.org/webfactory/doctrine-orm-test-infrastructure.svg?branch=master)](https://travis-ci.org/webfactory/doctrine-orm-test-infrastructure)
[![Coverage Status](https://img.shields.io/coveralls/webfactory/doctrine-orm-test-infrastructure.svg)](https://coveralls.io/r/webfactory/doctrine-orm-test-infrastructure?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webfactory/doctrine-orm-test-infrastructure/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webfactory/doctrine-orm-test-infrastructure/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ee876bf5-48d3-40ce-a488-3fafc5f776d7/mini.png)](https://insight.sensiolabs.com/projects/ee876bf5-48d3-40ce-a488-3fafc5f776d7)

This library provides some infrastructure for tests of Doctrine ORM entities, featuring:

- configuration of a SQLite in memory database, compromising well between speed and a database environment being both
  realistic and isolated 
- a mechanism for importing fixtures into your database that circumvents Doctrine's caching. This results in a more
  realistic test environment when loading entities from a repository.

[We](https://www.webfactory.de/) use it to test Doctrine repositories and entities in Symfony 2 bundles. In
applications, it's a lightweight alternative to the heavyweight [functional tests suggested in the Symfony documentation](http://symfony.com/doc/current/cookbook/testing/doctrine.html)
(we don't suggest you should skip those - we just want to open another path). In non application bundles, where
functional tests are not possible, it is our only way to test repositories and entities.


Installation
------------

Install via composer (see http://getcomposer.org/):

    composer require --dev webfactory/doctrine-orm-test-infrastructure


Usage
-----

    <?php
    
    use Entity\MyEntity;
    use Entity\MyEntityRepository;
    use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
    
    class MyEntityRepositoryTest extends \PHPUnit_Framework_TestCase
    {
        /** @var ORMInfrastructure */
        private $infrastructure;
        
        /** @var MyEntityRepository */
        private $repository;
        
        /** @see \PHPUnit_Framework_TestCase::setUp() */
        protected function setUp()
        {
            $this->infrastructure = ORMInfrastructure::createWithDependenciesFor(
                'Entity\MyEntity'
            );
            $this->repository = $this->infrastructure->getRepository('Entity\MyEntity');
        }
        
        /**
         * Example test: Asserts imported fixtures are retrieved with findAll().
         */
        public function testFindAllRetrievesFixtures()
        {
            $myEntityFixture = new MyEntity();
            $this->infrastructure->import($myEntityFixture);
            
            $entitiesLoadedFromDatabase = $this->repository->findAll();

            // Please note that you cannot do the following:
            // $this->assertContains($myEntityFixture, $entitiesLoadedFromDatabase);

            // But you can do things like this (you probably want to extract that in a convenient assertion method):
            $this->assertCount(1, $entitiesLoadedFromDatabase);
            $entityLoadedFromDatabase = $entitiesLoadedFromDatabase[0];
            $this->assertEquals($myEntityFixture->getId(), $entityLoadedFromDatabase->getId());
        }
        
        /**
         * Example test for retrieving Doctrine's entity manager.
         */
        public function testSomeFancyThingWithEntityManager()
        {
            $entityManager = $this->infrastructure->getEntityManager();
            // ...
        }
    }
    

Testing the library itself
--------------------------

After installing the dependencies managed via composer, just run

    vendor/bin/phpunit

from the library's root folder. This uses the shipped phpunit.xml.dist - feel free to create your own phpunit.xml if you
need local changes.

Happy testing!


## Changelog ##

### 1.4.6 -> 1.5.0 ###

- Introduced ``ConnectionConfiguration`` to explicitly define the type of database connection [(#15)](https://github.com/webfactory/doctrine-orm-test-infrastructure/pull/15)
- Added support for simple SQLite file databases via ``FileDatabaseConnectionConfiguration``; useful when data must persist for some time, but the connection is reset, e.g. in Symfony's [Functional Tests](http://symfony.com/doc/current/testing.html#functional-tests)


Create file-backed database:

    $configuration = new FileDatabaseConnectionConfiguration();
    $infrastructure = ORMInfrastructure::createOnlyFor(
        MyEntity::class,
        $configuration
    );
    // Used database file:
    echo $configuration->getDatabaseFile();

### 1.4.5 -> 1.4.6 ###

- Ignore associations against interfaces when detecting dependencies via ``ORMInfrastructure::createWithDependenciesFor`` to avoid errors
- Exposed event manager and created helper method to be able to register entity mappings


Register entity type mapping:

    $infrastructure->registerEntityMapping(EntityInterface::class, EntityImplementation::class);

Do not rely on this "feature" if you don't have to. Might be restructured in future versions.

### 1.4.4 -> 1.4.5 ###

- Fixed bug [#20](https://github.com/webfactory/doctrine-orm-test-infrastructure/issues/20): Entities might have been imported twice in case of bidirectional cascade
- Deprecated class ``Webfactory\Doctrine\ORMTestInfrastructure\DetachingObjectManagerDecorator`` (will be removed in next major release)

### 1.4.3 -> 1.4.4 ###

- Improved garbage collection
- Dropped support for PHP < 5.5
- Officially support PHP 7


Known Issues
------------

Please note that apart from any [open issues in this library](https://github.com/webfactory/doctrine-orm-test-infrastructure/issues), you
may stumble upon any Doctrine issues. Especially take care of it's [known sqlite issues](http://doctrine-dbal.readthedocs.org/en/latest/reference/known-vendor-issues.html#sqlite).


Performance Tests
-----------------

Several benchmarks have been created to keep track of the performance of the library.
Use the following command to run all benchmarks:
    
    php composer.phar benchmark
    
To avoid a [bug](http://bugs.xdebug.org/view.php?id=1070) in [Xdebug](http://xdebug.org/),
debugging is automatically disabled during the benchmark run.

Credits, Copyright and License
------------------------------

This bundle was started at webfactory GmbH, Bonn.

- <https://www.webfactory.de>
- <https://twitter.com/webfactory>

Copyright 2012-2017 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
