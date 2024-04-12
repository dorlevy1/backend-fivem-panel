<?php

namespace App;

use Illuminate\Database\Eloquent\Scope;

class StringifyScope implements Scope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function apply(
        \Illuminate\Database\Eloquent\Builder $builder,
        \Illuminate\Database\Eloquent\Model $model
    ): void {

        $hasPhp7GuidHelper = defined('\PDO::DBLIB_ATTR_STRINGIFY_UNIQUEIDENTIFIER');
        if ($hasPhp7GuidHelper):

            $model->getConnection()->getPdo()->setAttribute(\PDO::DBLIB_ATTR_STRINGIFY_UNIQUEIDENTIFIER, true);
        endif;
    }
}