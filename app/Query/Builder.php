<?php

namespace App\Query;

class Builder extends \MongoDB\Laravel\Query\Builder
{
    /** @internal This method is not supported by MongoDB. */
    public function toSql(): array
    {
        return $this->toMql();
    }

    /** @internal This method is not supported by MongoDB. */
    public function toRawSql(): array
    {
        return $this->toMql();
    }
}
