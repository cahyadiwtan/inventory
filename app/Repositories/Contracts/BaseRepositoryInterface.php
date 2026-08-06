<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    public function find(string $id): ?Model;

    public function findOrFail(string $id): Model;

    public function create(array $data): Model;

    public function update(Model|string $model, array $data): Model;

    public function delete(Model|string $model): bool;

    public function restore(Model|string $model): bool;

    public function findBy(string $field, mixed $value): ?Model;

    public function findByCriteria(array $criteria): ?Model;

    public function where(array $criteria): Collection;

    public function search(array $criteria, ?string $search = null, string $searchField = 'name'): LengthAwarePaginator;
}
