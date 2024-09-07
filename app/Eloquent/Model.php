<?php

namespace App\Eloquent;

use App\Query\Builder as QueryBuilder;
use MongoDB\Laravel\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    /** {@inheritdoc} */
    protected function newBaseQueryBuilder(): QueryBuilder
    {
        $connection = $this->getConnection();

        return new QueryBuilder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    }
}
