# Changelog for `webfactory/doctrine-orm-test-infrastructure`

This changelog tracks deprecations and changes breaking backwards compatibility. For more details on particular releases, consult the [GitHub releases page](https://github.com/webfactory/doctrine-orm-test-infrastructure/releases).

# Version 2.0

- The `ORMInfrastructure::create*()` methods by default read ORM mapping configuration through PHP attributes; annotations support has been removed in https://github.com/webfactory/doctrine-orm-test-infrastructure/pull/55/. You can, however, still create an instance of the `AnnotationDriver` mapping driver yourself (when using ORM 2.0) and pass it into these methods.

# Version 1.16

- The `\Webfactory\Doctrine\ORMTestInfrastructure\Query::getExecutionTimeInSeconds()` method has been deprecated without replacement in https://github.com/webfactory/doctrine-orm-test-infrastructure/pull/52, to prepare for the removal of the `DebugStack` class in Doctrine DBAL 4.0.
- Using annotation-based mapping as the default in `ORMInfrastructure::create*()` methods has been deprecated. Pass a mapping driver or upgrade `doctrine/orm` to >= 3.0 to switch to attributes-based mapping. Attributes-based configuration will be the default in the next major version.
