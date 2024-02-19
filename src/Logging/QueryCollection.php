<?php

namespace Webfactory\Doctrine\Logging;

class QueryCollection
{
    /** @var array<Query> */
    public array $queries = [];

    public bool $enabled = true;

    public function addQuery(Query $query): void
    {
        if ($this->enabled) {
            $this->queries[] = $query;
        }
    }
}