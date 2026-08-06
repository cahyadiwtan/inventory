<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->newQuery()->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->newQuery()->orderByDesc('created_at')->paginate($perPage, $columns);
    }

    public function find(string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(string $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Model|string $model, array $data): Model
    {
        $model = $this->resolve($model);
        $model->update($data);

        return $model;
    }

    public function delete(Model|string $model): bool
    {
        return (bool) $this->resolve($model)->delete();
    }

    public function restore(Model|string $model): bool
    {
        return (bool) $this->resolve($model)->restore();
    }

    public function findBy(string $field, mixed $value): ?Model
    {
        return $this->model->newQuery()->where($field, $value)->first();
    }

    public function findByCriteria(array $criteria): ?Model
    {
        return $this->applyCriteria($criteria)->first();
    }

    public function where(array $criteria): Collection
    {
        return $this->applyCriteria($criteria)->get();
    }

    public function search(array $criteria, ?string $search = null, string $searchField = 'name'): LengthAwarePaginator
    {
        $query = $this->applyCriteria($criteria);

        if ($search) {
            $query->where(function (Builder $q) use ($search, $searchField) {
                $q->where($searchField, 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate();
    }

    protected function applyCriteria(array $criteria): Builder
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query;
    }

    protected function resolve(Model|string $model): Model
    {
        if (is_string($model)) {
            return $this->findOrFail($model);
        }

        return $model;
    }
}
