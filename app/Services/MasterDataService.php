<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class MasterDataService
{
    /**
     * Generic CRUD service for master data entities (Category, Brand, Unit, Warehouse, Tax).
     */
    public function __construct(
        protected BaseRepositoryInterface $repository,
        protected ActivityLogService $activityLog,
    ) {}

    public function paginate(int $perPage = 15, ?string $search = null)
    {
        return $this->repository->search([], $search);
    }

    public function all()
    {
        return $this->repository->all();
    }

    public function find(string $id): ?Model
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Model
    {
        $data['is_active'] = Arr::get($data, 'is_active', true);

        $model = $this->repository->create($data);

        $this->activityLog->log('create', $model, "created {$this->label()}: {$model->name}");

        return $model;
    }

    public function update(string $id, array $data): Model
    {
        $data['is_active'] = Arr::get($data, 'is_active', true);

        $model = $this->repository->update($id, $data);

        $this->activityLog->log('update', $model, "updated {$this->label()}: {$model->name}");

        return $model;
    }

    public function delete(string $id): bool
    {
        $model = $this->repository->findOrFail($id);
        $name = $model->name;

        $result = $this->repository->delete($id);

        $this->activityLog->log('delete', $model, "deleted {$this->label()}: {$name}");

        return $result;
    }

    protected function label(): string
    {
        return strtolower(class_basename($this->repository));
    }
}
