<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasterResource;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

abstract class MasterApiController extends Controller
{
    public function __construct(
        protected BaseRepositoryInterface $repository,
        protected ActivityLogService $activityLog,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->get('search');

        return MasterResource::collection($this->repository->search([], $search));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rules());
        $data['is_active'] = $data['is_active'] ?? true;

        $model = $this->repository->create($data);

        $this->activityLog->log('create', $model, "created {$this->label()}: {$model->name}");

        return (new MasterResource($model))->response()->setStatusCode(201);
    }

    public function show(string $id)
    {
        return new MasterResource($this->repository->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $data = $this->validate($request, $this->rules());
        $data['is_active'] = $data['is_active'] ?? true;

        $model = $this->repository->update($id, $data);

        $this->activityLog->log('update', $model, "updated {$this->label()}: {$model->name}");

        return new MasterResource($model);
    }

    public function destroy(string $id)
    {
        $model = $this->repository->findOrFail($id);
        $this->repository->delete($id);

        $this->activityLog->log('delete', $model, "deleted {$this->label()}: {$model->name}");

        return response()->noContent();
    }

    abstract protected function rules(): array;

    abstract protected function label(): string;
}
