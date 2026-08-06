<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic repository for simple master data entities.
 */
class GenericRepository extends BaseRepository
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }
}
